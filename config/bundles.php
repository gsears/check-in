<?php

/*
bundles.php
Gareth Sears - 2493194S

Defines the external bundles used by symfony. These are 3rd party libraries.
*/

return [
    // The symfony framework
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    // Twig is the templating language
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    // Security bundle gives default configurations for users
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    // Web profiler is for debugging the application in development
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    // Monolog is the logging framework
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    // Maker bundle assists with development by generating boilerplate code where needed
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    // Fixtures are used to create sample DB data using the doctring ORM
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    // Adds a symfony specific version of Webpack for building frontend assets
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
    // Adds doctrine migrations support
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    // Doctrine test bundle rolls back the database after each functional / integration test
    DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],
    // Allows the creation of cronjob task services after initial configuration of crontab
    // A small script file is included for this in /bin/setup_cron.sh
    Rewieer\TaskSchedulerBundle\RewieerTaskSchedulerBundle::class => ['all' => true],
];
