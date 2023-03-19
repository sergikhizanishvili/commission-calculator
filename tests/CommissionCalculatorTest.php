<?php

namespace Sergi\TaskPhpRefactoring;

use PHPUnit\Framework\TestCase;

final class CommissionCalculatorTest extends TestCase {

    /**
     * Test class constructor
     */
    public function testClassConstructor()
    {
        $fileContent = file_get_contents(__DIR__ . '/input.txt');

        $CommissionCalculator = new CommissionCalculator($fileContent);

        $this->assertSame(explode("\n", $fileContent), $CommissionCalculator->transactions);
    }

    /**
     * Test calculation method
     */
    public function testcalculate() {

        $sampleRates = json_decode(file_get_contents(__DIR__ . '/sampleRates.json'));

        $mock = $this->createStub(\Sergi\TaskPhpRefactoring\CommissionService::class, 'getExchangeRates');
        $mock->expects($this->once())
            ->method('getExchangeRates')
            ->willReturn($sampleRates->rates);

        $mock->expects($this->any())
            ->method('isEu')
            ->willReturnOnConsecutiveCalls(true, true, false, false, false);

        $fileContent = file_get_contents(__DIR__ . '/input.txt');

        $CommissionCalculator = new CommissionCalculator($fileContent, $mock);
        
        $this->assertEquals(1, $CommissionCalculator->calculate(json_decode($CommissionCalculator->transactions[0])));
        $this->assertEquals(0.47, $CommissionCalculator->calculate(json_decode($CommissionCalculator->transactions[1])));
        $this->assertEquals(1.41, $CommissionCalculator->calculate(json_decode($CommissionCalculator->transactions[2])));
        $this->assertEquals(2.42, $CommissionCalculator->calculate(json_decode($CommissionCalculator->transactions[3])));
        $this->assertEquals(45.3, $CommissionCalculator->calculate(json_decode($CommissionCalculator->transactions[4])));
    }

    /**
     * Test commission output method
     */
    public function testcommissions() {

        $sampleRates = json_decode(file_get_contents(__DIR__ . '/sampleRates.json'));

        $mock = $this->createStub(\Sergi\TaskPhpRefactoring\CommissionService::class, 'getExchangeRates');
        $mock->expects($this->once())
            ->method('getExchangeRates')
            ->willReturn($sampleRates->rates);

        $mock->expects($this->any())
            ->method('isEu')
            ->willReturnOnConsecutiveCalls(true, true, false, false, false);

        $fileContent = file_get_contents(__DIR__ . '/input.txt');

        $CommissionCalculator = new CommissionCalculator($fileContent, $mock);

        $this->assertEquals([1, 0.47, 1.41, 2.42, 45.3], $CommissionCalculator->commissions());
    }
}