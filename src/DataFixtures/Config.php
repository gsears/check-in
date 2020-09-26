<?php

namespace App\DataFixtures;

class Config
{
    const TEST_STUDENT_EMAIL = 'test@student.gla.ac.uk';
    const TEST_INSTUCTOR_EMAIL = 'test@glasgow.ac.uk';

    const TERM_DATES = [
        'firstTerm' => [
            'start' => '21 September 2020',
            'end' => '04 December 2020',
        ],
        'secondTerm' => [
            'start' => '11 January 2021',
            'end' => '26 March 2021',
        ]
    ];

    const AFFECTIVE_FIELD_INTEREST = 'interest';
    const AFFECTIVE_FIELD_DIFFICULTY = 'difficulty';
    const AFFECTIVE_FIELD_FAMILIARITY = 'familiarity';
    const AFFECTIVE_FIELD_PLAN = 'ability to plan';
    const AFFECTIVE_FIELD_IMPROVEMENT = 'improvement';
    const AFFECTIVE_FIELD_SATISFACTION = 'satisfaction';

    const AFFECTIVE_FIELDS = [
        self::AFFECTIVE_FIELD_DIFFICULTY => [
            'high' => 'easy',
            'low' => 'hard'
        ],
        self::AFFECTIVE_FIELD_INTEREST => [
            'high' => 'interesting',
            'low' => 'boring'
        ],
        self::AFFECTIVE_FIELD_FAMILIARITY => [
            'high' => 'familiar',
            'low' => 'unfamiliar'
        ],
        self::AFFECTIVE_FIELD_PLAN  => [
            'high' => 'easy to plan',
            'low' => 'unable to plan'
        ],
        self::AFFECTIVE_FIELD_IMPROVEMENT => [
            'high' => 'skills improved',
            'low' => 'skills not improved'
        ],
        self::AFFECTIVE_FIELD_SATISFACTION => [
            'high' => 'triumphant',
            'low' => 'frustrated'
        ]
    ];

    const XY_QUESTIONS = [
        "interest-difficulty" => [
            "index" => 1,
            "text" => "How interesting did you find the task? How difficult did you *personally* find it?",
            "fields" => [self::AFFECTIVE_FIELD_INTEREST, self::AFFECTIVE_FIELD_DIFFICULTY],
        ],
        'planning-familiarity' => [
            "index" => 2,
            "text" => "How easy was it to plan how you'd execute the task? How familiar was the material?",
            "fields" => [self::AFFECTIVE_FIELD_PLAN, self::AFFECTIVE_FIELD_FAMILIARITY],
        ],
        'satisfaction-improvement' => [
            "index" => 3,
            "text" => "How did you feel while executing the task? Do you feel like your skills have improved?",
            "fields" =>  [self::AFFECTIVE_FIELD_SATISFACTION, self::AFFECTIVE_FIELD_IMPROVEMENT],
        ]
    ];

    const SENTIMENT_QUESTIONS = [
        "journal" => [
            "index" => 4,
            "text" => "Reflect on the course so far. Think about Course Materials, Workload, Time Management, Communication with Faculty, Coursework, Lectures and anything else you want to communicate.",
        ]
    ];
}
