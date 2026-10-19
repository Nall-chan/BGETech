<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die Vorlagen unter templates/ gegen die Checkliste der Symcon-Dokumentation
 * (https://www.symcon.de/de/service/dokumentation/modulreferenz/geraete/modbus-rtu-tcp/vorlagen/).
 */
class TemplateTest extends TestCase
{
    private const AddressFields = [
        'Active'            => 'boolean',
        'Name'              => 'string',
        'Ident'             => 'string',
        'Translation'       => 'array',
        'DataType'          => 'integer',
        'ReadFunctionCode'  => 'integer',
        'ReadAddress'       => 'integer',
        'WriteFunctionCode' => 'integer',
        'WriteAddress'      => 'integer',
        'Factor'            => 'integer',
        'Length'            => 'integer',
        'ByteOrder'         => 'integer',
        'Profile'           => 'string'
    ];
    /** Anzahl Register je DataType (Strings: Length/2) */
    private const Registers = [1 => 1, 12 => 1, 2 => 1, 3 => 2, 11 => 4, 4 => 1, 13 => 1, 5 => 1, 6 => 2, 8 => 4, 7 => 2, 9 => 4];
    /** Variablentyp je DataType ohne Faktor (1 = Integer, 2 = Float, 3 = String) */
    private const VariableType = [1 => 1, 12 => 1, 2 => 1, 3 => 1, 11 => 2, 4 => 1, 13 => 1, 5 => 1, 6 => 1, 8 => 2, 7 => 2, 9 => 2, 10 => 3, 14 => 3];
    /** SDM630 live geprüft: höchstens 80 Register je Anfrage */
    private const BlockLimit = 80;

    public static function templates(): array
    {
        $Files = [];
        foreach (glob(__DIR__ . '/../templates/*.json') as $File) {
            $Files[basename($File)] = [$File];
        }
        return $Files;
    }

    public function testTemplatesExist(): void
    {
        $this->assertGreaterThanOrEqual(14, count(self::templates()));
    }

    #[DataProvider('templates')]
    public function testStructure(string $File): void
    {
        $Template = json_decode(file_get_contents($File), true);
        $this->assertIsArray($Template, 'Kein gültiges JSON');
        foreach (['Addresses', 'VirtualAddresses', 'Profiles'] as $Key) {
            $this->assertArrayHasKey($Key, $Template);
        }
        $Idents = [];
        foreach ($Template['Addresses'] as $Address) {
            foreach (self::AddressFields as $Field => $Type) {
                $this->assertArrayHasKey($Field, $Address, $Address['Name'] ?? '?');
                // Factor ist eine Zahl: 0 als Ganzzahl, Faktoren wie 0.001 als Kommazahl
                $Types = $Field == 'Factor' ? ['integer', 'double'] : [$Type];
                $this->assertContains(gettype($Address[$Field]), $Types, $Address['Name'] . ': ' . $Field);
            }
            if (in_array($Address['DataType'], [0, 10, 14])) {
                $this->assertEquals(0, $Address['Factor'], 'Kein Faktor bei BOOL und String: ' . $Address['Name']);
            }
            $this->assertNotSame('', $Address['Ident'], $Address['Name']);
            $this->assertNotContains($Address['Ident'], $Idents, 'Doppelter Ident');
            $Idents[] = $Address['Ident'];
            $this->assertSame('de', $Address['Translation'][0]['Language'] ?? '', $Address['Name']);
            $this->assertNotSame('', $Address['Translation'][0]['Name'] ?? '', $Address['Name']);
            $this->assertContains($Address['ReadFunctionCode'], [3, 4], $Address['Name']);
            $this->assertContains($Address['WriteFunctionCode'], [0, 6, 16], $Address['Name']);
            if ($Address['WriteFunctionCode'] == 6) {
                $this->assertSame(1, self::Registers[$Address['DataType']] ?? 0, 'Funktion 6 nur für 1 Register: ' . $Address['Name']);
            }
            if (in_array($Address['DataType'], [10, 14])) {
                $this->assertGreaterThan(0, $Address['Length'], $Address['Name']);
            } else {
                $this->assertSame(0, $Address['Length'], $Address['Name']);
            }
        }
    }

    #[DataProvider('templates')]
    public function testProfiles(string $File): void
    {
        $Template = json_decode(file_get_contents($File), true);
        $this->assertIsArray($Template['Profiles']);
        foreach ($Template['Addresses'] as $Address) {
            $Profile = $Address['Profile'];
            if (!str_starts_with($Profile, 'BGETech.')) {
                continue;
            }
            $this->assertArrayHasKey($Profile, $Template['Profiles'], 'Profil fehlt in der Vorlage');
            $Expected = $Address['Factor'] != 0 ? 2 : self::VariableType[$Address['DataType']];
            $this->assertSame($Expected, $Template['Profiles'][$Profile]['Type'], 'Profiltyp passt nicht: ' . $Address['Name']);
            foreach (['Type', 'Prefix', 'Suffix', 'MinValue', 'MaxValue', 'StepSize', 'Digits', 'Icon', 'Associations'] as $Field) {
                $this->assertArrayHasKey($Field, $Template['Profiles'][$Profile], $Profile);
            }
        }
    }

    #[DataProvider('templates')]
    public function testActiveAddressesAreInBlocks(string $File): void
    {
        $Template = json_decode(file_get_contents($File), true);
        if (($Template['Requests']['Type'] ?? 0) != 1) {
            $this->assertGreaterThan(0, $Template['Requests']['Interval']);
            return;
        }
        $Blocks = $Template['Requests']['DataBlocks'];
        foreach ($Blocks as $Block) {
            $this->assertLessThanOrEqual(self::BlockLimit, $Block['Quantity']);
            $this->assertGreaterThan(0, $Block['Poller']);
        }
        foreach ($Template['Addresses'] as $Address) {
            if (!$Address['Active']) {
                continue;
            }
            $Size = in_array($Address['DataType'], [10, 14]) ? intdiv($Address['Length'] + 1, 2) : self::Registers[$Address['DataType']];
            $Covered = false;
            foreach ($Blocks as $Block) {
                if (($Block['Function'] == $Address['ReadFunctionCode']) && ($Address['ReadAddress'] >= $Block['Address']) && ($Address['ReadAddress'] + $Size <= $Block['Address'] + $Block['Quantity'])) {
                    $Covered = true;
                    break;
                }
            }
            $this->assertTrue($Covered, 'Nicht in einem Datenblock: ' . $Address['Name']);
        }
    }

    public function testGeneratorOutputIsUpToDate(): void
    {
        $Before = [];
        foreach (glob(__DIR__ . '/../templates/*.json') as $File) {
            $Before[basename($File)] = file_get_contents($File);
        }
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../tools/build-templates.php'), $Output, $Result);
        $this->assertSame(0, $Result, implode("\n", $Output));
        foreach ($Before as $Name => $Content) {
            $this->assertSame($Content, file_get_contents(__DIR__ . '/../templates/' . $Name), $Name . ' weicht vom Generator ab');
        }
    }
}
