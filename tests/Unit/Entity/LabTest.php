<?php

/*
LabTest.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Unit\Entity;

use App\Entity\Lab;
use App\Entity\LabXYQuestion;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the LabTest entity.
 */
class LabTest extends TestCase
{
    /**
     * Ensure questions are returned in index order.
     */
    public function testQuestionsReturnedInIndexOrder()
    {
        $lab = new Lab();

        $questionMockOrder3 = $this->createMock(LabXYQuestion::class);
        $questionMockOrder3
            ->method('getIndex')
            ->willReturn(3);

        $questionMockOrder2 = $this->createMock(LabXYQuestion::class);
        $questionMockOrder2
            ->method('getIndex')
            ->willReturn(2);

        $questionMockOrder1 = $this->createMock(LabXYQuestion::class);
        $questionMockOrder1
            ->method('getIndex')
            ->willReturn(1);

        $lab->addLabXYQuestion($questionMockOrder3);
        $lab->addLabXYQuestion($questionMockOrder1);
        $lab->addLabXYQuestion($questionMockOrder2);

        $this->assertEquals([
            $questionMockOrder1,
            $questionMockOrder2,
            $questionMockOrder3
        ], $lab->getQuestions()->toArray());

        $this->assertEquals(3, $lab->getQuestionCount());
    }
}
