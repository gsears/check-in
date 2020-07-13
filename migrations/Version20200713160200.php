<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200713160200 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dog DROP CONSTRAINT dog_ownerid_fkey');
        $this->addSql('ALTER TABLE attendance DROP CONSTRAINT attendance_dogid_fkey');
        $this->addSql('ALTER TABLE dog DROP CONSTRAINT dog_kennelname_fkey');
        $this->addSql('ALTER TABLE dog DROP CONSTRAINT dog_breedname_fkey');
        $this->addSql('ALTER TABLE attendance DROP CONSTRAINT attendance_showname_fkey');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('DROP TABLE owner');
        $this->addSql('DROP TABLE dog');
        $this->addSql('DROP TABLE kennel');
        $this->addSql('DROP TABLE breed');
        $this->addSql('DROP TABLE show');
        $this->addSql('DROP TABLE attendance');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('CREATE TABLE owner (ownerid INT NOT NULL, name VARCHAR(32) DEFAULT NULL, phone VARCHAR(16) DEFAULT NULL, PRIMARY KEY(ownerid))');
        $this->addSql('CREATE TABLE dog (dogid INT NOT NULL, ownerid INT DEFAULT NULL, kennelname VARCHAR(64) DEFAULT NULL, breedname VARCHAR(64) DEFAULT NULL, name VARCHAR(32) DEFAULT NULL, mothername VARCHAR(64) DEFAULT NULL, fathername VARCHAR(64) DEFAULT NULL, PRIMARY KEY(dogid))');
        $this->addSql('CREATE UNIQUE INDEX dog_name_key ON dog (name)');
        $this->addSql('CREATE INDEX IDX_812C397D75DAD987 ON dog (ownerid)');
        $this->addSql('CREATE INDEX IDX_812C397DB90D7394 ON dog (kennelname)');
        $this->addSql('CREATE INDEX IDX_812C397DE88BA65D ON dog (breedname)');
        $this->addSql('CREATE TABLE kennel (kennelname VARCHAR(64) NOT NULL, address VARCHAR(64) DEFAULT NULL, phone VARCHAR(16) DEFAULT NULL, PRIMARY KEY(kennelname))');
        $this->addSql('CREATE TABLE breed (breedname VARCHAR(64) NOT NULL, PRIMARY KEY(breedname))');
        $this->addSql('CREATE TABLE show (showname VARCHAR(64) NOT NULL, opendate VARCHAR(12) NOT NULL, closedate VARCHAR(12) DEFAULT NULL, PRIMARY KEY(showname, opendate))');
        $this->addSql('CREATE TABLE attendance (dogid INT NOT NULL, showname VARCHAR(64) NOT NULL, opendate VARCHAR(12) NOT NULL, place INT DEFAULT NULL, PRIMARY KEY(dogid, showname, opendate))');
        $this->addSql('CREATE INDEX IDX_6DE30D9132936BD2756002F6 ON attendance (showname, opendate)');
        $this->addSql('CREATE INDEX IDX_6DE30D917AAD69CF ON attendance (dogid)');
        $this->addSql('ALTER TABLE dog ADD CONSTRAINT dog_ownerid_fkey FOREIGN KEY (ownerid) REFERENCES owner (ownerid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dog ADD CONSTRAINT dog_kennelname_fkey FOREIGN KEY (kennelname) REFERENCES kennel (kennelname) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dog ADD CONSTRAINT dog_breedname_fkey FOREIGN KEY (breedname) REFERENCES breed (breedname) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT attendance_showname_fkey FOREIGN KEY (showname, opendate) REFERENCES show (showname, opendate) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT attendance_dogid_fkey FOREIGN KEY (dogid) REFERENCES dog (dogid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE users');
    }
}
