<?php

namespace App\Tests\Unit\Entity;


use App\Entity\LabXYQuestion;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LabXYQuestionTest extends TestCase
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
    public function testIndex($index, $expectedPass)
    {
        if (!$expectedPass) {
            $this->expectException(InvalidArgumentException::class);
        }

        $labXYQuestion = new LabXYQuestion();
        $labXYQuestion->setIndex($index);

        $this->assertTrue(true);
    }
}
