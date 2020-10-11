<?php

/*
LabXYQuestionTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use App\Entity\LabXYQuestion;
use PHPUnit\Framework\TestCase;

/**
 * Tests to ensure methods for LabXYQuestion
 * have checks to ensure valid state in database.
 */
class LabXYQuestionTest extends TestCase
{
    public function indexProvider()
    {
        yield [0, true];
        yield [5, true];
    }

    /**
     * @dataProvider indexProvider
     */
    public function testValidIndex($index)
    {
        $labXYQuestion = new LabXYQuestion();
        $labXYQuestion->setIndex($index);

        $this->assertTrue(true);
    }

    public function invalidIndexProvider()
    {
        yield [-2, false];
        yield [-1, false];
    }

    /**
     * @dataProvider invalidIndexProvider
     */
    public function testInvalidIndex($index)
    {
        $this->expectException(\InvalidArgumentException::class);

        $labXYQuestion = new LabXYQuestion();
        $labXYQuestion->setIndex($index);

        $this->fail();
    }
}
