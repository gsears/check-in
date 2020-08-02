<?php

/*
LabXYQuestionTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use App\Entity\LabXYQuestion;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Checks that SurveyQuestionInterface classes have valid state
 */
class SurveyQuestionInterfaceTest extends TestCase
{
    public function indexProvider()
    {
        yield [0, true];
        yield [-2, false];
        yield [5, true];
        yield [-1, false];
    }

    /**
     * @dataProvider indexProvider
     */
    public function testLabXYQuestionHasValidIndex($index, $expectedPass)
    {
        if (!$expectedPass) {
            $this->expectException(InvalidArgumentException::class);
        }

        $labXYQuestion = new LabXYQuestion();
        $labXYQuestion->setIndex($index);

        $this->assertTrue(true);
    }
}
