<?php

/*
FunctionalTestCase.php
Gareth Sears - 2493194S
*/

namespace App\Tests\Functional;

use App\DataFixtures\EntityCreator;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    private static $databaseCreated;
    private $entityCreator;

    protected static function createClient(array $options = [], array $server = [])
    {
        // Creating a client after interacting with services is not allowed,
        // so shutdown the kernel first
        // https://github.com/symfony/symfony/issues/34507
        // https://github.com/symfony/symfony-docs/pull/13351
        static::ensureKernelShutdown();

        return parent::createClient($options, $server);
    }

    protected function getService($service)
    {
        if (!static::$container) {
            static::bootKernel();
        }

        return static::$container->get($service);
    }

    /**
     * Get the doctrine entity manager. The database will be created if it
     * hasn't been already.
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        $this->ensureDatabaseExists();

        return $this->getService(EntityManagerInterface::class);
    }

    /**
     * Ensure the database schema has been created.
     */
    protected function ensureDatabaseExists()
    {
        if (self::$databaseCreated) {
            return;
        }

        $em = $this->getService(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());
        // The phpunit listener started a transaction already.
        // Commit it to actually save the schema, then start
        // a new transaction which will be rolled back at the end
        // of the test.
        StaticDriver::commit();
        StaticDriver::beginTransaction();

        self::$databaseCreated = true;
    }

    protected function getEntityCreator(): EntityCreator
    {
        if (!$this->entityCreator) {
            return new EntityCreator($this->getEntityManager());
        }

        return $this->entityCreator;
    }

    protected function persist($entity)
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    protected function assertEntityExists($class, array $fields)
    {
        $entity = $this->getEntityManager()
            ->getRepository($class)
            ->findOneBy($fields);

        $this->assertInstanceOf($class, $entity);
    }
}
