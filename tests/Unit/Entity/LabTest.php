<?php

namespace App\Tests;

use App\Entity\Lab;
use App\Entity\LabXYQuestion;
use PHPUnit\Framework\TestCase;

class LabTest extends TestCase
{
    public function questionProvider()
    {
        yield [
            [
                [
                    'type' => LabXYQuestion::class,
                    'index' => 2
                ],
                [
                    'type' => LabXYQuestion::class,
                    'index' => 1
                ],
                [
                    'type' => LabXYQuestion::class,
                    'index' => 0
                ]
            ],
            [0, 1, 2],
            3

        ];
    }
    /**
     * Returns SurveyQuestionInterfaces in index order
     *
     * @dataProvider questionProvider
     */
    public function testQuestions(array $questions, array $expectedOrder, int $count)
    {
        $lab = new Lab();

        foreach ($questions as $question) {

            if ($question['type'] === LabXYQuestion::class) {
                $labXYQuestion = $this->createMock(LabXYQuestion::class);
                $labXYQuestion
                    ->method('getIndex')
                    ->willReturn($question['index']);

                $lab->addLabXYQuestion($labXYQuestion);
            }
        }

        $questions = $lab->getQuestions()->toArray();
        $indices = [];

        foreach ($questions as $question) {
            $indices[] = $question->getIndex();
        }

        $this->assertEquals($expectedOrder, $indices);
        $this->assertEquals($count, $lab->getQuestionCount());
    }
}
