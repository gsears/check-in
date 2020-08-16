<?php

/*
LabSentimentQuestionTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use App\Entity\LabSentimentQuestion;
use PHPUnit\Framework\TestCase;

class LabSentimentQuestionTest extends TestCase
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
    public function testEnsureValidIndex($index, $expectedPass)
    {
        if (!$expectedPass) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $labXYQuestion = new LabSentimentQuestion();
        $labXYQuestion->setIndex($index);

        $this->assertTrue(true);
    }
}
