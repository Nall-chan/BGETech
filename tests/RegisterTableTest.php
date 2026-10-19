<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/autoload.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die Registertabellen der Zähler-Module und die Migration bestehender Instanzen.
 */
class RegisterTableTest extends TestCase
{
    private const SDM630 = '{BBCA5E14-505E-4394-B653-8CD33AD52037}';
    private const DRS210C = '{2CA41C9F-355C-4231-90A5-6D83A90B65BD}';

    protected function setUp(): void
    {
        IPS\Kernel::reset();
        // Nur die benötigten Module laden: der Ordner templates enthält kein Modul.
        $LibraryID = json_decode(file_get_contents(__DIR__ . '/../library.json'), true)['id'];
        IPS\ModuleLoader::loadSingleModule(__DIR__ . '/../SDM630', $LibraryID);
        IPS\ModuleLoader::loadSingleModule(__DIR__ . '/../DRS210C', $LibraryID);
        parent::setUp();
    }

    /**
     * Liefert alle Zähler-Module mit ihrer Registertabelle (Name, Typ, Profil, Adresse, Funktion, Anzahl).
     */
    public static function meterModules(): array
    {
        $Modules = [];
        foreach (glob(__DIR__ . '/../*/module.php') as $File) {
            $Source = file_get_contents($File);
            if (!str_contains($Source, 'extends BGETech')) {
                continue;
            }
            preg_match_all("/\['([^']+)',\s*(VARIABLETYPE_\w+),\s*'([^']*)',\s*(0x[0-9A-Fa-f]+),\s*(\d+),\s*(\d+),\s*(true|false)\]/", $Source, $Matches, PREG_SET_ORDER);
            $Modules[basename(dirname($File))] = [dirname($File), $Matches];
        }
        return $Modules;
    }

    #[DataProvider('meterModules')]
    public function testIdentsAreUnique(string $Dir, array $Registers): void
    {
        $this->assertNotEmpty($Registers, 'Keine Register gefunden in ' . $Dir);
        $Idents = array_map(fn (array $Register): string => str_replace(' ', '', $Register[1]), $Registers);
        $this->assertSame([], array_values(array_diff_assoc($Idents, array_unique($Idents))), 'Doppelte Idents in ' . basename($Dir));
    }

    #[DataProvider('meterModules')]
    public function testNamesAreTranslated(string $Dir, array $Registers): void
    {
        $German = json_decode(file_get_contents($Dir . '/locale.json'), true)['translations']['de'];
        foreach ($Registers as $Register) {
            $this->assertArrayHasKey($Register[1], $German, 'Fehlende Übersetzung in ' . basename($Dir));
        }
        $this->assertArrayHasKey('Instance has no active parent.', $German);
    }

    #[DataProvider('meterModules')]
    public function testRegisterDefinitions(string $Dir, array $Registers): void
    {
        foreach ($Registers as $Register) {
            $this->assertContains((int) $Register[5], [3, 4], 'Funktion ' . $Register[1]);
            $this->assertContains((int) $Register[6], [1, 2, 4], 'Anzahl Register ' . $Register[1]);
            // Energiewerte heißen Energie, nicht Leistung
            if (in_array($Register[3], ['Electricity', 'kVArh', 'kVAh', 'Electricity.I'])) {
                $this->assertStringNotContainsString('power', strtolower($Register[1]), 'Energiewert ' . $Register[1]);
            }
        }
    }

    public function testMigrateFixesRegistersOfExistingInstance(): void
    {
        $InstanceID = IPS_CreateInstance(self::SDM630);
        $Module = IPS\InstanceManager::getInstanceInterface($InstanceID);
        $Rows = json_decode(IPS_GetProperty($InstanceID, 'Variables'), true);
        // Stand vor Version 4.0 nachbilden
        foreach ($Rows as &$Row) {
            switch ($Row['Ident']) {
                case 'L3totalactiveenergy':
                    $Row['Address'] = 5770;
                    $Row['Name'] = 'Eigener Name';
                    $Row['Keep'] = false;
                    break;
                case 'Totalsystempowerfactorangle':
                    $Row['Ident'] = 'Totalsystempowerfactor';
                    break;
                case 'Apparentenergysincelastreset':
                    $Row['Ident'] = 'Reactiveenergysincelastreset';
                    $Row['Profile'] = 'Electricity';
                    break;
            }
        }
        unset($Row);
        $Rows = array_values(array_filter($Rows, fn (array $Row): bool => $Row['Ident'] != 'Chargesincelastreset'));
        $Old = ['configuration' => ['Interval' => 0, 'Variables' => json_encode($Rows)], 'attributes' => new stdClass()];

        $Result = $Module->Migrate(json_encode($Old));
        $this->assertNotSame('', $Result);
        $this->assertStringContainsString('"attributes":{}', $Result, 'Leere Attribute müssen ein Objekt bleiben');
        $New = [];
        foreach (json_decode(json_decode($Result, true)['configuration']['Variables'], true) as $Row) {
            $this->assertArrayNotHasKey($Row['Ident'], $New, 'Doppelter Ident ' . $Row['Ident']);
            $New[$Row['Ident']] = $Row;
        }
        // Adresse korrigiert, Name und Aktiv vom Nutzer erhalten
        $this->assertSame(0x016A, $New['L3totalactiveenergy']['Address']);
        $this->assertSame('Eigener Name', $New['L3totalactiveenergy']['Name']);
        $this->assertFalse($New['L3totalactiveenergy']['Keep']);
        // Früher doppelter Ident: beide Register getrennt
        $this->assertSame(0x003E, $New['Totalsystempowerfactor']['Address']);
        $this->assertSame(0x00FE, $New['Totalsystempowerfactorangle']['Address']);
        // Falsch benanntes Register über die Adresse zugeordnet, fehlendes ergänzt
        $this->assertArrayNotHasKey('Reactiveenergysincelastreset', $New);
        $this->assertSame('kVAh', $New['Apparentenergysincelastreset']['Profile']);
        $this->assertSame(0x0052, $New['Chargesincelastreset']['Address']);
        $this->assertCount(count($Rows) + 1, $New);
    }

    public function testMigrateWithoutChangesReturnsEmpty(): void
    {
        $InstanceID = IPS_CreateInstance(self::DRS210C);
        $Module = IPS\InstanceManager::getInstanceInterface($InstanceID);
        $Current = ['configuration' => ['Interval' => 0, 'Variables' => IPS_GetProperty($InstanceID, 'Variables')], 'attributes' => new stdClass()];
        $this->assertSame('', $Module->Migrate(json_encode($Current)));
    }
}
