<?php

declare(strict_types=1);

/*
 * Erzeugt die Vorlagen für die Symcon-Instanz "ModBus Gerät" (Ordner .templates/, Submodul ModBus-Templates)
 * aus den Registertabellen und Übersetzungen der Zähler-Module dieser Library.
 *
 * Aufruf: php .tools/build-templates.php
 *
 * Quellen der zusätzlichen Konfigurationsregister: Datenblätter unter docs/ (siehe .templates/README.md).
 * Format: https://www.symcon.de/de/service/dokumentation/modulreferenz/geraete/modbus-rtu-tcp/vorlagen/
 */

const ROOT = __DIR__ . '/..';
/** SDM630 live geprüft: höchstens 80 Register je Anfrage, Lücken im Block erlaubt */
const BLOCK_LIMIT = 80;
const POLL_VALUES = 5000;
const POLL_CONFIG = 300000;
/** Blöcke, die nur inaktive Register enthalten: werden erst nach dem Aktivieren gebraucht */
const POLL_INACTIVE = 60000;

// DataType der Vorlage
const DT_UINT16 = 2;
const DT_INT16 = 5;
const DT_UINT32 = 3;
const DT_INT32 = 6;
const DT_FLOAT32 = 7;
const DT_STRING = 10;
const DT_STRING_HEX = 14;

/** Profile der Library => Profile der Vorlage */
const PROFILE_MAP = [
    ''              => '',
    'Volt.230'      => '~Volt.230',
    'Hertz.50'      => '~Hertz.50',
    'Ampere'        => '~Ampere',
    'Watt.14490'    => '~Watt',
    'Power'         => '~Power',
    'Electricity'   => '~Electricity',
    'VA'            => 'BGETech.VA',
    'VaR'           => 'BGETech.VAr',
    'PhaseAngle'    => 'BGETech.PhaseAngle',
    'Intensity.F'   => 'BGETech.Percent',
    'kVArh'         => 'BGETech.kVArh',
    'kVAh'          => 'BGETech.kVAh',
    'Ah'            => 'BGETech.Ah',
    'Volt.I'        => 'BGETech.Volt.I',
    'Hertz.I'       => 'BGETech.Hertz.I',
    'Ampere.I'      => 'BGETech.Ampere.I',
    'Watt.I'        => 'BGETech.Watt.I',
    'VA.I'          => 'BGETech.VA.I',
    'VaR.I'         => 'BGETech.VAr.I',
    'Electricity.I' => 'BGETech.Electricity.I',
];

/** Baudraten-Codes der Eastron-Zähler (Register 40029) */
const EASTRON_BAUD = [0 => '2400', 1 => '4800', 2 => '9600', 3 => '19200', 4 => '38400', 5 => '1200'];

/**
 * Gerätespezifische Einstellungen.
 * blocks: Datenblöcke verwenden (nur wo Lücken geprüft oder Bereiche lückenlos sind)
 * config: zusätzliche Register zur Konfiguration und Geräteinformation
 */
function devices(): array
{
    $eastron = function (array $Baud, bool $Parity = true): array
    {
        $Config = [];
        if ($Parity) {
            $Config[] = cfg('Parity and stop bits', 'Parität und Stoppbits', DT_FLOAT32, 3, 0x0012, 16, profile: 'BGETech.Eastron.ParityStop');
        }
        $Config[] = cfg('Modbus address', 'Modbus-Adresse', DT_FLOAT32, 3, 0x0014, 16, profile: 'BGETech.Eastron.ModbusAddress');
        $Config[] = cfg('Baud rate', 'Baudrate', DT_FLOAT32, 3, 0x001C, 16, profile: 'BGETech.Eastron.Baud.' . implode('', $Baud));
        return $Config;
    };
    return [
        'SDM630'       => ['blocks' => true, 'config' => $eastron([0, 1, 2, 3, 4])],
        'SDM530'       => ['blocks' => true, 'config' => []],
        'SDM230'       => ['blocks' => true, 'config' => $eastron([0, 1, 2, 5])],
        'SDM220'       => ['blocks' => true, 'config' => []],
        'SDM120C'      => ['blocks' => true, 'config' => $eastron([0, 1, 2, 5], false)],
        'SDM120ModBus' => ['blocks' => true, 'config' => $eastron([0, 1, 2, 5])],
        'SDM72D'       => ['blocks' => true, 'config' => $eastron([0, 1, 2, 5])],
        'SDM72DMV2'    => ['blocks' => true, 'config' => array_merge($eastron([0, 1, 2, 3, 5]), [
            cfg('Serial number', 'Seriennummer', DT_UINT32, 3, 0xFC00, active: false),
            cfg('Meter code', 'Gerätecode', DT_STRING_HEX, 3, 0xFC02, length: 2, active: false),
        ])],
        'SmartX965C'   => ['blocks' => true, 'config' => array_merge($eastron([0, 1, 2, 3, 4]), transformerRatios())],
        // Lückenlose Bereiche 0x000E-0x003B und 0x0100-0x012F
        'DRT428M'      => ['blocks' => true, 'config' => [
            cfg('Serial number', 'Seriennummer', DT_STRING_HEX, 3, 0x0000, length: 4),
            cfg('Modbus address', 'Modbus-Adresse', DT_UINT16, 3, 0x0002, 6, profile: 'BGETech.ModbusAddress'),
            // Annahme: Register enthält die Baudrate selbst (wie beim DRS 210-C), Datenblatt nennt nur "HEX 300 ... 9600"
            cfg('Baud rate', 'Baudrate', DT_UINT16, 3, 0x0003, 6, profile: 'BGETech.Baud'),
            cfg('Software version', 'Softwareversion', DT_FLOAT32, 3, 0x0004),
            cfg('Hardware version', 'Hardwareversion', DT_FLOAT32, 3, 0x0006),
            cfg('Total energy calculation', 'Berechnung Gesamtenergie', DT_UINT16, 3, 0x000B, 6, profile: 'BGETech.DRT428M.Combined'),
        ]],
        // Kein Datenblock möglich (Registerlücken, live geprüft). Schreiben nur mit Seriennummer im Telegramm, daher nur lesend.
        'DRS210C'      => ['blocks' => false, 'interval' => 10000, 'config' => [
            cfg('Serial number', 'Seriennummer', DT_STRING_HEX, 3, 0x1000, length: 8, active: false),
            cfg('Meter code', 'Gerätecode', DT_STRING_HEX, 3, 0x1010, length: 4, active: false),
            cfg('Modbus address', 'Modbus-Adresse', DT_UINT16, 3, 0x1018, active: false),
            cfg('Baud rate', 'Baudrate', DT_UINT16, 3, 0x1020, profile: 'BGETech.Baud', active: false),
            cfg('Meter name', 'Gerätename', DT_STRING, 3, 0x1030, length: 16, active: false),
            cfg('Protocol version', 'Protokollversion', DT_FLOAT32, 3, 0x1050, active: false),
            cfg('Software version', 'Softwareversion', DT_FLOAT32, 3, 0x1054, active: false),
            cfg('Hardware version', 'Hardwareversion', DT_FLOAT32, 3, 0x1058, active: false),
            cfg('Maximum current', 'Maximalstrom', DT_UINT16, 3, 0x1060, profile: 'BGETech.Ampere.I', active: false),
            cfg('Total energy calculation', 'Berechnung Gesamtenergie', DT_UINT16, 3, 0x107A, profile: 'BGETech.DRS210C.Accumulation', active: false),
        ]],
        'DRS458'       => ['blocks' => false, 'interval' => 10000, 'config' => []],
        // Byte-Reihenfolge und Skalierung laut Datenblatt unklar: zwei Varianten für Tests mit Gerät
        'DRT710M'      => ['blocks' => false, 'interval' => 10000, 'config' => [], 'variants' => [
            'DRT710M_WordSwap'  => ['byteOrder' => 3],
            'DRT710M_BigEndian' => ['byteOrder' => 0],
        ]],
        'WS100'        => ws100(),
        // Nur als Vorlage (kein Zähler-Modul), Quellen: docs/SDM630MCT.pdf, SDM54_de.pdf, SDM72CT-M.pdf, SDM18-Usermanual-v1.1.pdf, dcm230user manual v2.0.pdf
        // Wie SDM630, ohne 0x00FE (nicht im Datenblatt)
        'SDM630MCT'    => ['base' => 'SDM630', 'exclude' => [0x00FE], 'blocks' => true, 'registers' => array_merge(phaseEnergies(), resettableEnergies(), [
            cfg('Total system reactive power demand', 'Blindleistungsbedarf gesamt', DT_FLOAT32, 4, 0x006C, profile: 'BGETech.VAr', active: false),
            cfg('Maximum total system reactive power demand', 'Maximaler Blindleistungsbedarf gesamt', DT_FLOAT32, 4, 0x006E, profile: 'BGETech.VAr', active: false),
        ]), 'config' => array_merge($eastron([0, 1, 2, 3, 4]), [
            // Schreiben nur mit Passwort (Register 40025), nicht bei MID-Zählern
            cfg('CT ratio', 'Stromwandlerverhältnis', DT_FLOAT32, 3, 0x003E, 16),
            cfg('PT ratio', 'Spannungswandlerverhältnis', DT_FLOAT32, 3, 0x0040, 16),
        ])],
        // Wie SDM630 (0x00FE im Datenblatt ohne Namen, daher übernommen), dazu Phasen, Leistungsbedarf je Phase und Tarife (-2T)
        // Nicht übernommen: 0x155E-0x157E (Tarif-Maxima), dort ist das Datenblatt widersprüchlich
        'SDM54'        => ['base' => 'SDM630', 'blocks' => true, 'registers' => array_merge(phaseEnergies(), phasePowerDemand(), tariffs54()), 'config' => array_merge($eastron([0, 1, 2, 3, 4]), eastronInfo(false))],
        // Wie SDM72DM-V2 ohne rückstellbare Zähler und Import-/Exportleistung, dazu Blindenergie
        'SDM72CT'      => ['base' => 'SDM72DMV2', 'exclude' => [0x0180, 0x0184, 0x0186, 0x018C, 0x0500, 0x0502], 'blocks' => true, 'registers' => [
            cfg('Total import reactive energy', 'aufgenommene Blindenergie', DT_FLOAT32, 4, 0x004C, profile: 'BGETech.kVArh'),
            cfg('Total export reactive energy', 'abgegebene Blindenergie', DT_FLOAT32, 4, 0x004E, profile: 'BGETech.kVArh'),
        ], 'config' => array_merge($eastron([0, 1, 2, 5]), [
            // Schreiben nur mit Freigabe (KPPA)
            cfg('CT ratio', 'Stromwandlerverhältnis', DT_FLOAT32, 3, 0x0020, 16),
            cfg('Serial number', 'Seriennummer', DT_UINT32, 3, 0xFC00, active: false),
        ])],
        // Registergleich mit SDM120-ModBus
        'SDM18'        => ['base' => 'SDM120ModBus', 'blocks' => true, 'config' => array_merge($eastron([0, 1, 2, 5]), eastronInfo(true))],
        // Wie SDM630 plus Phasenenergien (docs/SDM630-TCP Modbus TCP protocol V1.0.pdf, höchstens 50 Werte je Anfrage).
        // Keine Baudrate/Parität; Ethernet-Parameter nur lesend (falsche IP macht den Zähler unerreichbar).
        // Auch für SDM630MCT-ML-TCP im Modus "mehrere Adressen" (je Kanal eine Unit-ID).
        'SDM630TCP'    => ['base' => 'SDM630', 'blocks' => true, 'registers' => phaseEnergies(), 'config' => [
            cfg('Modbus address', 'Modbus-Adresse', DT_FLOAT32, 3, 0x0014, 16, profile: 'BGETech.Eastron.ModbusAddress'),
            cfg('Ethernet parameters', 'Ethernet-Parameter', DT_STRING_HEX, 3, 0xF006, length: 14, active: false),
        ]],
        // Wie Smart X96-5C ohne die im Protokoll fehlenden Register (docs/X96-5A_protocol_v1.6.pdf)
        'SmartX965A'   => ['base' => 'SmartX965C', 'exclude' => [0x0050, 0x0070, 0x0072, 0x0074, 0x0076, 0x00FE, 0x0108], 'blocks' => true, 'registers' => x965a(), 'config' => array_merge($eastron([0, 1, 2, 3, 4]), transformerRatios(), [
            cfg('Serial number', 'Seriennummer', DT_UINT32, 3, 0xFC00, active: false),
        ])],
        'DCM232'       => ['blocks' => true, 'registers' => dcm232(0), 'config' => array_merge($eastron([0, 1, 2, 3, 4]), [
            cfg('Serial number', 'Seriennummer', DT_UINT32, 3, 0xFC00, active: false),
        ]), 'variants' => ['DCM232_CH1' => ['byteOrder' => 0]]],
        'DCM232CH2'    => ['blocks' => true, 'registers' => dcm232(3000), 'config' => [], 'variants' => ['DCM232_CH2' => ['byteOrder' => 0]]],
        // Messwerte zusätzlich lückenlos ab 0x4000 (Register 316385 ff.), daher dieser Bereich
        'DCM230'       => ['blocks' => true, 'registers' => [
            cfg('Voltage', 'Spannung', DT_FLOAT32, 4, 0x4000, profile: '~Volt'),
            cfg('Current', 'Strom', DT_FLOAT32, 4, 0x4002, profile: '~Ampere'),
            cfg('Active power', 'Wirkleistung', DT_FLOAT32, 4, 0x4004, profile: '~Watt'),
            cfg('Import active energy', 'aufgenommene Wirkenergie', DT_FLOAT32, 4, 0x4006, profile: '~Electricity'),
            cfg('Export active energy', 'abgegebene Wirkenergie', DT_FLOAT32, 4, 0x4008, profile: '~Electricity'),
            cfg('Total system power demand', 'Wirkleistungsbedarf', DT_FLOAT32, 4, 0x400A, profile: '~Watt', active: false),
            cfg('Maximum total system power demand', 'Maximaler Wirkleistungsbedarf', DT_FLOAT32, 4, 0x400C, profile: '~Watt', active: false),
            cfg('Total active energy', 'Gesamte Wirkenergie', DT_FLOAT32, 4, 0x400E, profile: '~Electricity'),
            cfg('Resettable total active energy', 'Gesamte Wirkenergie (rückstellbar)', DT_FLOAT32, 4, 0x4010, profile: '~Electricity', active: false),
        ], 'config' => array_merge($eastron([0, 1, 2, 3, 5]), [
            cfg('Serial number', 'Seriennummer', DT_UINT32, 3, 0xFC00, active: false),
        ])],
    ];
}

/**
 * Beschreibt ein zusätzliches Register.
 */
function cfg(string $Name, string $German, int $DataType, int $ReadFunction, int $Address, int $WriteFunction = 0, int $length = 0, string $profile = '', bool $active = true, float $factor = 0): array
{
    return compact('Name', 'German', 'DataType', 'ReadFunction', 'Address', 'WriteFunction', 'length', 'profile', 'active', 'factor');
}

/**
 * Eastron: Bezug und Lieferung je Phase (0x015A-0x0176), inaktiv.
 */
function phaseEnergies(): array
{
    $Registers = [];
    $Address = 0x015A;
    foreach ([['active', 'Wirkenergie', '~Electricity'], ['reactive', 'Blindenergie', 'BGETech.kVArh']] as [$Kind, $German, $Profile]) {
        foreach ([['import', 'aufgenommene'], ['export', 'abgegebene']] as [$Direction, $GermanDirection]) {
            for ($Phase = 1; $Phase <= 3; $Phase++) {
                $Registers[] = cfg('L' . $Phase . ' ' . $Direction . ' ' . $Kind . ' energy', 'L' . $Phase . ' ' . $GermanDirection . ' ' . $German, DT_FLOAT32, 4, $Address, profile: $Profile, active: false);
                $Address += 2;
            }
        }
        // Danach folgen die Summen je Phase (L1-L3 total), die schon in der Registertabelle des SDM630 stehen
        $Address = 0x016C;
    }
    return $Registers;
}

/**
 * Eastron: rückstellbare Zähler (0x0180-0x018A), inaktiv.
 */
function resettableEnergies(): array
{
    return [
        cfg('Resettable total active energy', 'Gesamte Wirkenergie (rückstellbar)', DT_FLOAT32, 4, 0x0180, profile: '~Electricity', active: false),
        cfg('Resettable total reactive energy', 'Gesamte Blindenergie (rückstellbar)', DT_FLOAT32, 4, 0x0182, profile: 'BGETech.kVArh', active: false),
        cfg('Resettable import active energy', 'aufgenommene Wirkenergie (rückstellbar)', DT_FLOAT32, 4, 0x0184, profile: '~Electricity', active: false),
        cfg('Resettable export active energy', 'abgegebene Wirkenergie (rückstellbar)', DT_FLOAT32, 4, 0x0186, profile: '~Electricity', active: false),
        cfg('Resettable import reactive energy', 'aufgenommene Blindenergie (rückstellbar)', DT_FLOAT32, 4, 0x0188, profile: 'BGETech.kVArh', active: false),
        cfg('Resettable export reactive energy', 'abgegebene Blindenergie (rückstellbar)', DT_FLOAT32, 4, 0x018A, profile: 'BGETech.kVArh', active: false),
    ];
}

/**
 * SDM54: Wirkleistungsbedarf je Phase (0x0A06-0x0A36), inaktiv.
 */
function phasePowerDemand(): array
{
    $Registers = [];
    for ($Phase = 1; $Phase <= 3; $Phase++) {
        $Registers[] = cfg('Power demand L' . $Phase, 'Wirkleistungsbedarf L' . $Phase, DT_FLOAT32, 4, 0x0A06 + 2 * ($Phase - 1), profile: '~Watt', active: false);
        $Registers[] = cfg('Maximum power demand L' . $Phase, 'Maximaler Wirkleistungsbedarf L' . $Phase, DT_FLOAT32, 4, 0x0A32 + 2 * ($Phase - 1), profile: '~Watt', active: false);
    }
    return $Registers;
}

/**
 * SDM54(-2T): Energie je Tarif (0x130C-0x1336) und je Phase und Tarif (0x133C-0x1382), inaktiv.
 */
function tariffs54(): array
{
    $Registers = [];
    $Groups = [
        [0x130C, 'Total active energy', 'Gesamte Wirkenergie', '~Electricity'],
        [0x1314, 'Import active energy', 'aufgenommene Wirkenergie', '~Electricity'],
        [0x131C, 'Export active energy', 'abgegebene Wirkenergie', '~Electricity'],
        [0x1324, 'Total reactive energy', 'Gesamte Blindenergie', 'BGETech.kVArh'],
        [0x132C, 'Import reactive energy', 'aufgenommene Blindenergie', 'BGETech.kVArh'],
        [0x1334, 'Export reactive energy', 'abgegebene Blindenergie', 'BGETech.kVArh'],
    ];
    foreach ($Groups as [$Address, $Name, $German, $Profile]) {
        for ($Tariff = 1; $Tariff <= 2; $Tariff++) {
            $Registers[] = cfg($Name . ' tariff ' . $Tariff, $German . ' Tarif ' . $Tariff, DT_FLOAT32, 4, $Address + 2 * ($Tariff - 1), profile: $Profile, active: false);
        }
    }
    $Address = 0x133C;
    for ($Tariff = 1; $Tariff <= 2; $Tariff++) {
        foreach ([['active', 'Wirkenergie', '~Electricity'], ['reactive', 'Blindenergie', 'BGETech.kVArh']] as [$Kind, $German, $Profile]) {
            foreach ([['import', 'aufgenommene'], ['export', 'abgegebene'], ['total', 'Gesamte']] as [$Direction, $GermanDirection]) {
                for ($Phase = 1; $Phase <= 3; $Phase++) {
                    $Registers[] = cfg('L' . $Phase . ' ' . $Direction . ' ' . $Kind . ' energy tariff ' . $Tariff, 'L' . $Phase . ' ' . $GermanDirection . ' ' . $German . ' Tarif ' . $Tariff, DT_FLOAT32, 4, $Address, profile: $Profile, active: false);
                    $Address += 2;
                }
            }
        }
    }
    return $Registers;
}

/**
 * Smart X96: Spannungs- und Stromwandlerverhältnis (40047-40053), Schreiben nur mit Freigabe (KPPA).
 */
function transformerRatios(): array
{
    return [
        cfg('PT primary voltage', 'Spannungswandler primär', DT_FLOAT32, 3, 0x002E, 16, profile: '~Volt'),
        cfg('PT secondary voltage', 'Spannungswandler sekundär', DT_FLOAT32, 3, 0x0030, 16, profile: '~Volt'),
        cfg('CT primary current', 'Stromwandler primär', DT_FLOAT32, 3, 0x0032, 16, profile: '~Ampere'),
        cfg('CT secondary current', 'Stromwandler sekundär', DT_FLOAT32, 3, 0x0034, 16, profile: '~Ampere'),
    ];
}

/**
 * Smart X96-5A: Phasenfolge, Lastart, Klirrfaktor gesamt je Phase, Minimal- und Maximalwerte.
 */
function x965a(): array
{
    $Registers = [
        cfg('Voltage phase sequence', 'Phasenfolge Spannung', DT_FLOAT32, 4, 0x00A0, profile: 'BGETech.X96.PhaseSequence'),
        cfg('Current phase sequence', 'Phasenfolge Strom', DT_FLOAT32, 4, 0x00A2, profile: 'BGETech.X96.PhaseSequence'),
        cfg('Nature of the load', 'Lastart', DT_FLOAT32, 4, 0x00C0, profile: 'BGETech.X96.LoadNature'),
    ];
    for ($Phase = 1; $Phase <= 3; $Phase++) {
        $Registers[] = cfg('Nature of the load L' . $Phase, 'Lastart L' . $Phase, DT_FLOAT32, 4, 0x00C0 + 2 * $Phase, profile: 'BGETech.X96.LoadNature', active: false);
    }
    for ($Phase = 1; $Phase <= 3; $Phase++) {
        $Registers[] = cfg('Total harmonic voltage L' . $Phase, 'Gesamtoberschwingungen Spannung L' . $Phase, DT_FLOAT32, 4, 0x047A + 2 * ($Phase - 1), profile: 'BGETech.Percent', active: false);
        $Registers[] = cfg('Total harmonic current L' . $Phase, 'Gesamtoberschwingungen Strom L' . $Phase, DT_FLOAT32, 4, 0x0480 + 2 * ($Phase - 1), profile: 'BGETech.Percent', active: false);
    }
    $Values = [
        ['current L1', 'Strom L1', '~Ampere'], ['current L2', 'Strom L2', '~Ampere'], ['current L3', 'Strom L3', '~Ampere'],
        ['neutral current', 'Neutralleiterstrom', '~Ampere'], ['sum of currents', 'Summe Strom', '~Ampere'],
        ['voltage L1', 'Spannung L1', '~Volt.230'], ['voltage L2', 'Spannung L2', '~Volt.230'], ['voltage L3', 'Spannung L3', '~Volt.230'],
        ['voltage L1 to L2', 'Spannung L1-L2', '~Volt'], ['voltage L2 to L3', 'Spannung L2-L3', '~Volt'], ['voltage L3 to L1', 'Spannung L3-L1', '~Volt'],
    ];
    foreach ([['Maximum', 'Maximum', 0x0A70], ['Minimum', 'Minimum', 0x0A9E]] as [$Kind, $German, $Start]) {
        foreach ($Values as $Index => [$Name, $GermanName, $Profile]) {
            $Registers[] = cfg($Kind . ' ' . $Name, $German . ' ' . $GermanName, DT_FLOAT32, 4, $Start + 2 * $Index, profile: $Profile, active: false);
        }
    }
    return array_merge($Registers, phaseEnergies());
}

/**
 * DCM232: Register eines Kanals. Im Modus "eine Adresse" liegt Kanal 2 um 3000 versetzt,
 * im Modus "zwei Adressen" nutzen beide Kanäle die Register von Kanal 1 (Handbuch Abschnitt 4.3.1).
 */
function dcm232(int $Offset): array
{
    $Registers = [
        cfg('Voltage', 'Spannung', DT_FLOAT32, 4, $Offset + 0x0000, profile: '~Volt'),
        cfg('Current', 'Strom', DT_FLOAT32, 4, $Offset + 0x0006, profile: '~Ampere'),
        cfg('Active power', 'Wirkleistung', DT_FLOAT32, 4, $Offset + 0x000C, profile: '~Watt'),
        cfg('Import active energy', 'aufgenommene Wirkenergie', DT_FLOAT32, 4, $Offset + 0x0048, profile: '~Electricity'),
        cfg('Export active energy', 'abgegebene Wirkenergie', DT_FLOAT32, 4, $Offset + 0x004A, profile: '~Electricity'),
        cfg('Total active energy', 'Gesamte Wirkenergie', DT_FLOAT32, 4, $Offset + 0x0156, profile: '~Electricity'),
    ];
    if ($Offset == 0) {
        // Register 320005 ff. liegen außerhalb der Kanalbereiche; Zuordnung zu den Kanälen nennt das Handbuch nicht
        array_push(
            $Registers,
            cfg('Positive line loss energy', 'Leitungsverlust Energie positiv', DT_FLOAT32, 4, 0x4E24, profile: '~Electricity', active: false),
            cfg('Negative line loss energy', 'Leitungsverlust Energie negativ', DT_FLOAT32, 4, 0x4E26, profile: '~Electricity', active: false),
            cfg('Line loss power', 'Leitungsverlust Leistung', DT_FLOAT32, 4, 0x4E28, profile: '~Watt', active: false),
            cfg('Line loss energy', 'Leitungsverlust Energie', DT_FLOAT32, 4, 0x4E2A, profile: '~Electricity', active: false),
            cfg('Net active energy import minus export', 'Wirkenergie Bezug minus Lieferung', DT_FLOAT32, 4, 0x4E3E, profile: '~Electricity', active: false),
            cfg('Net active energy export minus import', 'Wirkenergie Lieferung minus Bezug', DT_FLOAT32, 4, 0x4E42, profile: '~Electricity', active: false),
        );
    }
    return $Registers;
}

/**
 * Eastron: Seriennummer, Gerätecode und ggf. Softwareversion (0xFC00 ff.), inaktiv.
 */
function eastronInfo(bool $Software): array
{
    $Registers = [
        cfg('Serial number', 'Seriennummer', DT_UINT32, 3, 0xFC00, active: false),
        cfg('Meter code', 'Gerätecode', DT_STRING_HEX, 3, 0xFC02, length: 2, active: false),
    ];
    if ($Software) {
        $Registers[] = cfg('Software version', 'Softwareversion', DT_STRING_HEX, 3, 0xFC03, length: 2, active: false);
    }
    return $Registers;
}

/**
 * Register und Einstellungen des WS100-19xx (Quelle: docs/RWS100-19xx_DE.pdf, docs/WS100-19xx_DE.pdf).
 * Kein Zähler-Modul in der Library, daher eigene Registerliste.
 * Annahmen (ungeprüft): Funktion 3 und Big-Endian, beides nennt das Datenblatt nicht.
 * Format "INT32(3+3)" = 3 Vor- und 3 Nachkommastellen, daher Faktor 0,001.
 */
function ws100(): array
{
    $Registers = [
        cfg('Voltage', 'Spannung', DT_INT32, 3, 0x0100, profile: '~Volt.230', factor: 0.001),
        cfg('Current', 'Strom', DT_INT32, 3, 0x0102, profile: '~Ampere', factor: 0.001),
        cfg('Active power', 'Wirkleistung', DT_INT32, 3, 0x0104, profile: '~Watt', factor: 1),
        cfg('Apparent power', 'Scheinleistung', DT_INT32, 3, 0x0106, profile: 'BGETech.VA', factor: 1),
        cfg('Reactive power', 'Blindleistung', DT_INT32, 3, 0x0108, profile: 'BGETech.VAr', factor: 1),
        cfg('Frequency', 'Frequenz', DT_INT16, 3, 0x010A, profile: '~Hertz.50', factor: 0.1),
        cfg('Power factor', 'Leistungsfaktor', DT_INT16, 3, 0x010B, factor: 0.001),
    ];
    // Energie: je Gruppe Summe und Tarif 1-4 (ohne Tarifversion: Tarif 1 = Summe, Tarif 2-4 = 0)
    $Groups = [
        [0x010E, 'Total forward active energy', 'aufgenommene Wirkenergie', '~Electricity'],
        [0x0118, 'Total reverse active energy', 'abgegebene Wirkenergie', '~Electricity'],
        [0x0122, 'Total active energy', 'Gesamte Wirkenergie', '~Electricity'],
        [0x012C, 'Total forward reactive energy', 'aufgenommene Blindenergie', 'BGETech.kVArh'],
        [0x0136, 'Total reverse reactive energy', 'abgegebene Blindenergie', 'BGETech.kVArh'],
        [0x0140, 'Total reactive energy', 'Gesamte Blindenergie', 'BGETech.kVArh'],
        [0x014A, 'Reactive energy quadrant 1', 'Blindenergie Quadrant 1', 'BGETech.kVArh'],
        [0x0154, 'Reactive energy quadrant 2', 'Blindenergie Quadrant 2', 'BGETech.kVArh'],
        [0x015E, 'Reactive energy quadrant 3', 'Blindenergie Quadrant 3', 'BGETech.kVArh'],
        [0x0168, 'Reactive energy quadrant 4', 'Blindenergie Quadrant 4', 'BGETech.kVArh'],
    ];
    foreach ($Groups as $Index => [$Address, $Name, $German, $Profile]) {
        // Summen von Bezug, Lieferung und Gesamt sind aktiv, Quadranten und Tarife inaktiv
        $Registers[] = cfg($Name, $German, DT_INT32, 3, $Address, profile: $Profile, active: $Index < 6, factor: 0.01);
        for ($Tariff = 1; $Tariff <= 4; $Tariff++) {
            $Registers[] = cfg($Name . ' tariff ' . $Tariff, $German . ' Tarif ' . $Tariff, DT_INT32, 3, $Address + 2 * $Tariff, profile: $Profile, active: false, factor: 0.01);
        }
    }
    array_push(
        $Registers,
        cfg('Resettable total active energy', 'Gesamte Wirkenergie (rückstellbar)', DT_INT32, 3, 0x0172, profile: '~Electricity', active: false, factor: 0.01),
        cfg('Resettable total reactive energy', 'Gesamte Blindenergie (rückstellbar)', DT_INT32, 3, 0x0174, profile: 'BGETech.kVArh', active: false, factor: 0.01),
        cfg('Forward active power demand', 'Wirkleistungsbedarf Bezug', DT_INT32, 3, 0x0176, profile: '~Watt', active: false, factor: 0.1),
        cfg('Maximum forward active power demand', 'Maximaler Wirkleistungsbedarf Bezug', DT_INT32, 3, 0x0178, profile: '~Watt', active: false, factor: 0.1),
        cfg('Reverse active power demand', 'Wirkleistungsbedarf Lieferung', DT_INT32, 3, 0x017A, profile: '~Watt', active: false, factor: 0.1),
        cfg('Maximum reverse active power demand', 'Maximaler Wirkleistungsbedarf Lieferung', DT_INT32, 3, 0x017C, profile: '~Watt', active: false, factor: 0.1),
        cfg('Forward reactive power demand', 'Blindleistungsbedarf Bezug', DT_INT32, 3, 0x0180, profile: 'BGETech.VAr', active: false, factor: 0.1),
        cfg('Maximum forward reactive power demand', 'Maximaler Blindleistungsbedarf Bezug', DT_INT32, 3, 0x0182, profile: 'BGETech.VAr', active: false, factor: 0.1),
        cfg('Reverse reactive power demand', 'Blindleistungsbedarf Lieferung', DT_INT32, 3, 0x0184, profile: 'BGETech.VAr', active: false, factor: 0.1),
        cfg('Maximum reverse reactive power demand', 'Maximaler Blindleistungsbedarf Lieferung', DT_INT32, 3, 0x0186, profile: 'BGETech.VAr', active: false, factor: 0.1),
    );
    // Schreiben laut Datenblatt mit Funktion 0x10
    $Config = [
        cfg('Serial number', 'Seriennummer', DT_STRING_HEX, 3, 0x1000, length: 6),
        cfg('Modbus address', 'Modbus-Adresse', DT_UINT16, 3, 0x1003, 16, profile: 'BGETech.ModbusAddress'),
        cfg('Firmware version', 'Firmwareversion', DT_UINT16, 3, 0x1004),
        cfg('Hardware version', 'Hardwareversion', DT_UINT16, 3, 0x1005),
        cfg('Baud rate', 'Baudrate', DT_UINT16, 3, 0x100C, 16, profile: 'BGETech.WS100.Baud'),
        cfg('Parity', 'Parität', DT_UINT16, 3, 0x100D, 16, profile: 'BGETech.WS100.Parity'),
        cfg('Stop bits', 'Stoppbits', DT_UINT16, 3, 0x100E, 16, profile: 'BGETech.WS100.StopBits'),
        cfg('Total energy calculation', 'Berechnung Gesamtenergie', DT_UINT16, 3, 0x100F, 16, profile: 'BGETech.WS100.Combined'),
    ];
    return ['blocks' => true, 'gapless' => true, 'registers' => $Registers, 'config' => $Config];
}

/**
 * Liest die Registertabelle eines Zähler-Moduls.
 */
function registers(string $Module): array
{
    $Source = file_get_contents(ROOT . '/' . $Module . '/module.php');
    preg_match_all("/\['([^']+)',\s*VARIABLETYPE_(\w+),\s*'([^']*)',\s*(0x[0-9A-Fa-f]+),\s*(\d+),\s*(\d+),\s*(true|false)\]/", $Source, $Matches, PREG_SET_ORDER);
    $Registers = [];
    foreach ($Matches as [, $Name, $Type, $Profile, $Address, $Function, $Quantity, $Active]) {
        $Registers[] = [
            'Name'     => $Name,
            'Type'     => $Type,
            'Profile'  => $Profile,
            'Address'  => hexdec($Address),
            'Function' => (int) $Function,
            'Quantity' => (int) $Quantity,
            'Active'   => $Active == 'true'
        ];
    }
    return $Registers;
}

function ident(string $Name): string
{
    return str_replace(' ', '', ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', $Name)));
}

function dataType(string $Type, int $Quantity): int
{
    return match ([$Type, $Quantity]) {
        ['FLOAT', 2]   => DT_FLOAT32,
        ['INTEGER', 1] => DT_UINT16,
        ['INTEGER', 2] => DT_INT32,
        default        => throw new Exception('Unbekannter Typ ' . $Type . '/' . $Quantity),
    };
}

/** Anzahl Register eines Datentyps */
function size(int $DataType, int $Length): int
{
    return match ($DataType) {
        DT_UINT16, DT_INT16       => 1,
        DT_UINT32, DT_INT32, DT_FLOAT32 => 2,
        DT_STRING, DT_STRING_HEX  => intdiv($Length + 1, 2),
    };
}

function profile(string $Library, string $Name): string
{
    $Profile = PROFILE_MAP[$Library] ?? throw new Exception('Unbekanntes Profil ' . $Library);
    // Leiter-Leiter-Spannungen liegen außerhalb von ~Volt.230 (207-253 V)
    if (($Profile == '~Volt.230') && preg_match('/to line/i', $Name)) {
        return '~Volt';
    }
    return $Profile;
}

function address(string $Name, string $Ident, string $German, int $DataType, int $ReadFunction, int $Address, int $WriteFunction, int $Length, string $Profile, bool $Active, float $Factor = 0): array
{
    return [
        'Active'            => $Active,
        'Name'              => $Name,
        'Ident'             => $Ident,
        'Translation'       => [['Language' => 'de', 'Name' => $German]],
        'DataType'          => $DataType,
        'ReadFunctionCode'  => $ReadFunction,
        'ReadAddress'       => $Address,
        'WriteFunctionCode' => $WriteFunction,
        'WriteAddress'      => $WriteFunction ? $Address : 0,
        // 0 als Ganzzahl, damit die bisherigen Vorlagen unverändert bleiben
        'Factor'            => $Factor == 0 ? 0 : $Factor,
        'Length'            => $Length,
        'ByteOrder'         => -1,
        'Profile'           => $Profile
    ];
}

/**
 * Fasst Adressen gleicher Funktion zu Datenblöcken mit höchstens BLOCK_LIMIT Registern zusammen.
 * Auch inaktive Adressen liegen in einem Block, sonst würden sie nach dem Aktivieren nie gelesen.
 * Blöcke nur mit inaktiven Adressen werden seltener abgefragt (POLL_INACTIVE).
 * Mit $Gapless entstehen nur Blöcke ohne Lücken.
 */
function blocks(array $Addresses, int $Poller, bool $Gapless = false): array
{
    $ByFunction = [];
    foreach ($Addresses as $Address) {
        $ByFunction[$Address['ReadFunctionCode']][] = [$Address['ReadAddress'], size($Address['DataType'], $Address['Length']), $Address['Active']];
    }
    $Blocks = [];
    $Close = function (int $Function, int $Start, int $End, bool $Active) use (&$Blocks, $Poller): void
    {
        $Blocks[] = ['Function' => $Function, 'Address' => $Start, 'Quantity' => $End - $Start, 'Poller' => $Active ? $Poller : max($Poller, POLL_INACTIVE)];
    };
    ksort($ByFunction);
    foreach ($ByFunction as $Function => $Ranges) {
        sort($Ranges);
        $Start = null;
        $End = 0;
        $Active = false;
        foreach ($Ranges as [$Begin, $Count, $IsActive]) {
            if (($Start !== null) && (($Begin + $Count - $Start > BLOCK_LIMIT) || ($Gapless && ($Begin > $End)))) {
                $Close($Function, $Start, $End, $Active);
                $Start = null;
                $Active = false;
            }
            if ($Start === null) {
                $Start = $Begin;
            }
            $End = max($End, $Begin + $Count);
            $Active = $Active || $IsActive;
        }
        if ($Start !== null) {
            $Close($Function, $Start, $End, $Active);
        }
    }
    return $Blocks;
}

function floatProfile(string $Suffix, int $Digits, array $Associations = [], float $Min = 0, float $Max = 0): array
{
    return ['Type' => 2, 'Prefix' => '', 'Suffix' => $Suffix, 'MinValue' => $Min, 'MaxValue' => $Max, 'StepSize' => 0, 'Digits' => $Digits, 'Icon' => '', 'Associations' => $Associations];
}

function intProfile(string $Suffix, array $Associations = [], int $Min = 0, int $Max = 0): array
{
    return ['Type' => 1, 'Prefix' => '', 'Suffix' => $Suffix, 'MinValue' => $Min, 'MaxValue' => $Max, 'StepSize' => 0, 'Digits' => 0, 'Icon' => '', 'Associations' => $Associations];
}

function associations(array $Values, bool $Float): array
{
    $Result = [];
    foreach ($Values as $Value => $Name) {
        $Result[] = ['Value' => $Float ? (float) $Value : $Value, 'Name' => $Name, 'Icon' => '', 'Color' => -1];
    }
    return $Result;
}

/** Definitionen aller Profile mit Präfix BGETech. */
function profileDefinition(string $Name): array
{
    if (str_starts_with($Name, 'BGETech.Eastron.Baud.')) {
        $Codes = str_split(substr($Name, strlen('BGETech.Eastron.Baud.')));
        $Values = [];
        foreach ($Codes as $Code) {
            $Values[(int) $Code] = EASTRON_BAUD[(int) $Code] . ' bps';
        }
        return floatProfile('', 0, associations($Values, true));
    }
    return match ($Name) {
        'BGETech.VA'                   => floatProfile(' VA', 1),
        'BGETech.VAr'                  => floatProfile(' VAr', 1),
        'BGETech.PhaseAngle'           => floatProfile(' °', 1),
        'BGETech.Percent'              => floatProfile(' %', 1),
        'BGETech.kVArh'                => floatProfile(' kVArh', 2),
        'BGETech.kVAh'                 => floatProfile(' kVAh', 2),
        'BGETech.Ah'                   => floatProfile(' Ah', 2),
        'BGETech.Volt.I'               => intProfile(' V'),
        'BGETech.Hertz.I'              => intProfile(' Hz'),
        'BGETech.Ampere.I'             => intProfile(' A'),
        'BGETech.Watt.I'               => intProfile(' W'),
        'BGETech.VA.I'                 => intProfile(' VA'),
        'BGETech.VAr.I'                => intProfile(' VAr'),
        'BGETech.Electricity.I'        => intProfile(' kWh'),
        'BGETech.Baud'                 => intProfile(' bps'),
        'BGETech.ModbusAddress'        => intProfile('', [], 1, 247),
        'BGETech.Eastron.ModbusAddress'=> floatProfile('', 0, [], 1, 247),
        'BGETech.Eastron.ParityStop'   => floatProfile('', 0, associations([0 => '8N1', 1 => '8E1', 2 => '8O1', 3 => '8N2'], true)),
        'BGETech.DRT428M.Combined'     => intProfile('', associations([1 => 'Import', 5 => 'Import + Export', 9 => 'Import - Export'], false)),
        'BGETech.DRS210C.Accumulation' => intProfile('', associations([1 => 'Import', 3 => 'Import + Export', 4 => 'Import - Export'], false)),
        // Datenblatt: Codes 1-9, laut Hinweis nur 9600, 19200, 38400 und 115200 unterstützt
        'BGETech.WS100.Baud'           => intProfile('', associations([6 => '9600 bps', 7 => '19200 bps', 8 => '38400 bps', 9 => '115200 bps'], false)),
        'BGETech.WS100.Parity'         => intProfile('', associations([0 => 'None', 1 => 'Odd', 2 => 'Even'], false)),
        'BGETech.WS100.StopBits'       => intProfile('', associations([1 => '1', 2 => '2'], false)),
        'BGETech.X96.PhaseSequence'    => floatProfile('', 0, associations([1 => 'Normal', 2 => 'Reverse', 3 => '3'], true)),
        'BGETech.X96.LoadNature'       => floatProfile('', 0, associations([1 => 'Resistive', 2 => 'Inductive', 3 => 'Capacitive'], true)),
        'BGETech.WS100.Combined'       => intProfile('', associations([1 => 'Import', 2 => 'Export', 3 => 'Import + Export', 4 => 'Import - Export'], false)),
        default                        => throw new Exception('Unbekanntes Profil ' . $Name),
    };
}

function build(string $Module, array $Device, int $ByteOrder): array
{
    $Addresses = [];
    // Registertabelle eines Zähler-Moduls: das eigene oder das eines baugleichen Zählers (base).
    // Geräte ohne Zähler-Modul bringen ihre Registerliste ganz selbst mit (registers ohne base).
    $Base = $Device['base'] ?? (isset($Device['registers']) ? null : $Module);
    $German = $Base ? json_decode(file_get_contents(ROOT . '/' . $Base . '/locale.json'), true)['translations']['de'] : [];
    foreach ($Base ? registers($Base) : [] as $Register) {
        if (in_array($Register['Address'], $Device['exclude'] ?? [])) {
            continue;
        }
        $Addresses[] = address(
            $Register['Name'],
            ident($Register['Name']),
            $German[$Register['Name']] ?? throw new Exception($Base . ': Übersetzung fehlt für ' . $Register['Name']),
            dataType($Register['Type'], $Register['Quantity']),
            $Register['Function'],
            $Register['Address'],
            0,
            0,
            profile($Register['Profile'], $Register['Name']),
            $Register['Active']
        );
    }
    // Zusätzliche Register des Geräts
    foreach ($Device['registers'] ?? [] as $Register) {
        $Addresses[] = address($Register['Name'], ident($Register['Name']), $Register['German'], $Register['DataType'], $Register['ReadFunction'], $Register['Address'], $Register['WriteFunction'], $Register['length'], $Register['profile'], $Register['active'], $Register['factor']);
    }
    $Config = [];
    foreach ($Device['config'] as $Register) {
        $Config[] = address($Register['Name'], ident($Register['Name']), $Register['German'], $Register['DataType'], $Register['ReadFunction'], $Register['Address'], $Register['WriteFunction'], $Register['length'], $Register['profile'], $Register['active'], $Register['factor']);
    }
    $Idents = array_column(array_merge($Addresses, $Config), 'Ident');
    if (count($Idents) != count(array_unique($Idents))) {
        throw new Exception($Module . ': doppelte Idents ' . implode(', ', array_diff_assoc($Idents, array_unique($Idents))));
    }
    if ($Device['blocks']) {
        // Messwerte und Konfiguration getrennt, damit die Konfiguration selten gelesen wird
        $Gapless = $Device['gapless'] ?? false;
        $Requests = ['Type' => 1, 'Interval' => POLL_VALUES, 'DataBlocks' => array_merge(blocks($Addresses, POLL_VALUES, $Gapless), blocks($Config, POLL_CONFIG, $Gapless))];
    } else {
        $Requests = ['Type' => 0, 'Interval' => $Device['interval'], 'DataBlocks' => []];
    }
    $Profiles = [];
    foreach (array_merge($Addresses, $Config) as $Address) {
        if (str_starts_with($Address['Profile'], 'BGETech.')) {
            $Profiles[$Address['Profile']] = profileDefinition($Address['Profile']);
        }
    }
    ksort($Profiles);
    return [
        'Addresses'        => array_merge($Addresses, $Config),
        'VirtualAddresses' => [],
        'Profiles'         => (object) $Profiles,
        'ByteOrder'        => $ByteOrder,
        'Requests'         => $Requests
    ];
}

foreach (devices() as $Module => $Device) {
    $Variants = $Device['variants'] ?? [$Module => ['byteOrder' => 0]];
    foreach ($Variants as $File => $Variant) {
        $Template = build($Module, $Device, $Variant['byteOrder']);
        $Json = json_encode($Template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION) . "\n";
        file_put_contents(ROOT . '/.templates/' . $File . '.json', $Json);
        printf("%-18s %3d Adressen, %s\n", $File, count($Template['Addresses']), $Template['Requests']['Type'] ? count($Template['Requests']['DataBlocks']) . ' Blöcke' : 'Einzelabfrage');
    }
}
