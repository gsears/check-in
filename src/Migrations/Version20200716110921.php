<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716110921 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE course_instance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE enrolment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE course_instance (id INT NOT NULL, course_id VARCHAR(12) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EB84DC88591CC992 ON course_instance (course_id)');
        $this->addSql('CREATE TABLE course_instance_instructor (course_instance_id INT NOT NULL, instructor_id INT NOT NULL, PRIMARY KEY(course_instance_id, instructor_id))');
        $this->addSql('CREATE INDEX IDX_3FBB60CC4E3F42C9 ON course_instance_instructor (course_instance_id)');
        $this->addSql('CREATE INDEX IDX_3FBB60CC8C4FC193 ON course_instance_instructor (instructor_id)');
        $this->addSql('CREATE TABLE course (code VARCHAR(12) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(65535) DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE TABLE enrolment (id INT NOT NULL, student_id INT NOT NULL, course_instance_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C04D5114CB944F1A ON enrolment (student_id)');
        $this->addSql('CREATE INDEX IDX_C04D51144E3F42C9 ON enrolment (course_instance_id)');
        $this->addSql('ALTER TABLE course_instance ADD CONSTRAINT FK_EB84DC88591CC992 FOREIGN KEY (course_id) REFERENCES course (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_instance_instructor ADD CONSTRAINT FK_3FBB60CC4E3F42C9 FOREIGN KEY (course_instance_id) REFERENCES course_instance (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_instance_instructor ADD CONSTRAINT FK_3FBB60CC8C4FC193 FOREIGN KEY (instructor_id) REFERENCES instructor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE enrolment ADD CONSTRAINT FK_C04D5114CB944F1A FOREIGN KEY (student_id) REFERENCES student (guid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE enrolment ADD CONSTRAINT FK_C04D51144E3F42C9 FOREIGN KEY (course_instance_id) REFERENCES course_instance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE course_instance_instructor DROP CONSTRAINT FK_3FBB60CC4E3F42C9');
        $this->addSql('ALTER TABLE enrolment DROP CONSTRAINT FK_C04D51144E3F42C9');
        $this->addSql('ALTER TABLE course_instance DROP CONSTRAINT FK_EB84DC88591CC992');
        $this->addSql('DROP SEQUENCE course_instance_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE enrolment_id_seq CASCADE');
        $this->addSql('DROP TABLE course_instance');
        $this->addSql('DROP TABLE course_instance_instructor');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE enrolment');
    }
}
