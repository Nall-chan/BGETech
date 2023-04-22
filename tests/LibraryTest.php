<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateDRS210C(): void
    {
        $this->validateModule(__DIR__ . '/../DRS210C');
    }

    public function testValidateDRS458(): void
    {
        $this->validateModule(__DIR__ . '/../DRS458');
    }

    public function testValidateDRT428M(): void
    {
        $this->validateModule(__DIR__ . '/../DRT428M');
    }
    public function testValidateDRT710M(): void
    {
        $this->validateModule(__DIR__ . '/../DRT710M');
    }

    public function testValidateSDM72D(): void
    {
        $this->validateModule(__DIR__ . '/../SDM72D');
    }
    public function testValidateSDM72DMV2(): void
    {
        $this->validateModule(__DIR__ . '/../SDM72DMV2');
    }
    public function testValidateSDM120C(): void
    {
        $this->validateModule(__DIR__ . '/../SDM120C');
    }
    public function testValidateSDM120ModBus(): void
    {
        $this->validateModule(__DIR__ . '/../SDM120ModBus');
    }
    public function testValidateSDM220(): void
    {
        $this->validateModule(__DIR__ . '/../SDM220');
    }
    public function testValidateSDM230(): void
    {
        $this->validateModule(__DIR__ . '/../SDM230');
    }
    public function testValidateSDM530(): void
    {
        $this->validateModule(__DIR__ . '/../SDM530');
    }
    public function testValidateSDM630(): void
    {
        $this->validateModule(__DIR__ . '/../SDM630');
    }
    public function testValidateSmartX965C(): void
    {
        $this->validateModule(__DIR__ . '/../SmartX965C');
    }
}