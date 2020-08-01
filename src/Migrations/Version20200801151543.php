<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200801151543 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE instructor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE course_instance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE xyquestion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_xyquestion_response_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_xyquestion_danger_zone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_xyquestion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_response_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE affective_field_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE enrolment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE instructor (id INT NOT NULL, appuser_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31FC43DDBB5E5996 ON instructor (appuser_id)');
        $this->addSql('CREATE TABLE lab (id INT NOT NULL, course_instance_id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, start_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_61D6B1C44E3F42C9 ON lab (course_instance_id)');
        $this->addSql('CREATE TABLE course_instance (id INT NOT NULL, course_id VARCHAR(12) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, index_in_course INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EB84DC88591CC992 ON course_instance (course_id)');
        $this->addSql('CREATE TABLE course_instance_instructor (course_instance_id INT NOT NULL, instructor_id INT NOT NULL, PRIMARY KEY(course_instance_id, instructor_id))');
        $this->addSql('CREATE INDEX IDX_3FBB60CC4E3F42C9 ON course_instance_instructor (course_instance_id)');
        $this->addSql('CREATE INDEX IDX_3FBB60CC8C4FC193 ON course_instance_instructor (instructor_id)');
        $this->addSql('CREATE TABLE xyquestion (id INT NOT NULL, x_field_id INT NOT NULL, y_field_id INT NOT NULL, name VARCHAR(255) NOT NULL, question_text VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_89C14EB38AEAFB64 ON xyquestion (x_field_id)');
        $this->addSql('CREATE INDEX IDX_89C14EB36528905A ON xyquestion (y_field_id)');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, email VARCHAR(180) NOT NULL, forename VARCHAR(180) NOT NULL, surname VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE lab_xyquestion_response (id INT NOT NULL, lab_xyquestion_id INT NOT NULL, lab_response_id INT DEFAULT NULL, x_value INT NOT NULL, y_value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA476E87FA80A32 ON lab_xyquestion_response (lab_xyquestion_id)');
        $this->addSql('CREATE INDEX IDX_DA476E8771FEB27B ON lab_xyquestion_response (lab_response_id)');
        $this->addSql('CREATE TABLE student (guid INT NOT NULL, appuser_id INT NOT NULL, PRIMARY KEY(guid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B723AF33BB5E5996 ON student (appuser_id)');
        $this->addSql('CREATE TABLE lab_xyquestion_danger_zone (id INT NOT NULL, lab_xyquestion_id INT NOT NULL, risk_level INT NOT NULL, y_max INT NOT NULL, y_min INT NOT NULL, x_max INT NOT NULL, x_min INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_35A632B0FA80A32 ON lab_xyquestion_danger_zone (lab_xyquestion_id)');
        $this->addSql('CREATE TABLE course (code VARCHAR(12) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(65535) DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE TABLE lab_xyquestion (id INT NOT NULL, lab_id INT NOT NULL, xy_question_id INT NOT NULL, index INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E19299A8628913D5 ON lab_xyquestion (lab_id)');
        $this->addSql('CREATE INDEX IDX_E19299A8EA320F6 ON lab_xyquestion (xy_question_id)');
        $this->addSql('CREATE TABLE lab_response (id INT NOT NULL, student_id INT NOT NULL, lab_id INT NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, submitted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E4772066CB944F1A ON lab_response (student_id)');
        $this->addSql('CREATE INDEX IDX_E4772066628913D5 ON lab_response (lab_id)');
        $this->addSql('CREATE TABLE affective_field (id INT NOT NULL, name VARCHAR(255) NOT NULL, low_label VARCHAR(255) NOT NULL, high_label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE enrolment (id INT NOT NULL, student_id INT NOT NULL, course_instance_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C04D5114CB944F1A ON enrolment (student_id)');
        $this->addSql('CREATE INDEX IDX_C04D51144E3F42C9 ON enrolment (course_instance_id)');
        $this->addSql('ALTER TABLE instructor ADD CONSTRAINT FK_31FC43DDBB5E5996 FOREIGN KEY (appuser_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab ADD CONSTRAINT FK_61D6B1C44E3F42C9 FOREIGN KEY (course_instance_id) REFERENCES course_instance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_instance ADD CONSTRAINT FK_EB84DC88591CC992 FOREIGN KEY (course_id) REFERENCES course (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_instance_instructor ADD CONSTRAINT FK_3FBB60CC4E3F42C9 FOREIGN KEY (course_instance_id) REFERENCES course_instance (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_instance_instructor ADD CONSTRAINT FK_3FBB60CC8C4FC193 FOREIGN KEY (instructor_id) REFERENCES instructor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyquestion ADD CONSTRAINT FK_89C14EB38AEAFB64 FOREIGN KEY (x_field_id) REFERENCES affective_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyquestion ADD CONSTRAINT FK_89C14EB36528905A FOREIGN KEY (y_field_id) REFERENCES affective_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_xyquestion_response ADD CONSTRAINT FK_DA476E87FA80A32 FOREIGN KEY (lab_xyquestion_id) REFERENCES lab_xyquestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_xyquestion_response ADD CONSTRAINT FK_DA476E8771FEB27B FOREIGN KEY (lab_response_id) REFERENCES lab_response (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33BB5E5996 FOREIGN KEY (appuser_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_xyquestion_danger_zone ADD CONSTRAINT FK_35A632B0FA80A32 FOREIGN KEY (lab_xyquestion_id) REFERENCES lab_xyquestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_xyquestion ADD CONSTRAINT FK_E19299A8628913D5 FOREIGN KEY (lab_id) REFERENCES lab (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_xyquestion ADD CONSTRAINT FK_E19299A8EA320F6 FOREIGN KEY (xy_question_id) REFERENCES xyquestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_response ADD CONSTRAINT FK_E4772066CB944F1A FOREIGN KEY (student_id) REFERENCES student (guid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_response ADD CONSTRAINT FK_E4772066628913D5 FOREIGN KEY (lab_id) REFERENCES lab (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE enrolment ADD CONSTRAINT FK_C04D5114CB944F1A FOREIGN KEY (student_id) REFERENCES student (guid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE enrolment ADD CONSTRAINT FK_C04D51144E3F42C9 FOREIGN KEY (course_instance_id) REFERENCES course_instance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE course_instance_instructor DROP CONSTRAINT FK_3FBB60CC8C4FC193');
        $this->addSql('ALTER TABLE lab_xyquestion DROP CONSTRAINT FK_E19299A8628913D5');
        $this->addSql('ALTER TABLE lab_response DROP CONSTRAINT FK_E4772066628913D5');
        $this->addSql('ALTER TABLE lab DROP CONSTRAINT FK_61D6B1C44E3F42C9');
        $this->addSql('ALTER TABLE course_instance_instructor DROP CONSTRAINT FK_3FBB60CC4E3F42C9');
        $this->addSql('ALTER TABLE enrolment DROP CONSTRAINT FK_C04D51144E3F42C9');
        $this->addSql('ALTER TABLE lab_xyquestion DROP CONSTRAINT FK_E19299A8EA320F6');
        $this->addSql('ALTER TABLE instructor DROP CONSTRAINT FK_31FC43DDBB5E5996');
        $this->addSql('ALTER TABLE student DROP CONSTRAINT FK_B723AF33BB5E5996');
        $this->addSql('ALTER TABLE lab_response DROP CONSTRAINT FK_E4772066CB944F1A');
        $this->addSql('ALTER TABLE enrolment DROP CONSTRAINT FK_C04D5114CB944F1A');
        $this->addSql('ALTER TABLE course_instance DROP CONSTRAINT FK_EB84DC88591CC992');
        $this->addSql('ALTER TABLE lab_xyquestion_response DROP CONSTRAINT FK_DA476E87FA80A32');
        $this->addSql('ALTER TABLE lab_xyquestion_danger_zone DROP CONSTRAINT FK_35A632B0FA80A32');
        $this->addSql('ALTER TABLE lab_xyquestion_response DROP CONSTRAINT FK_DA476E8771FEB27B');
        $this->addSql('ALTER TABLE xyquestion DROP CONSTRAINT FK_89C14EB38AEAFB64');
        $this->addSql('ALTER TABLE xyquestion DROP CONSTRAINT FK_89C14EB36528905A');
        $this->addSql('DROP SEQUENCE instructor_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE course_instance_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE xyquestion_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_xyquestion_response_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_xyquestion_danger_zone_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_xyquestion_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_response_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE affective_field_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE enrolment_id_seq CASCADE');
        $this->addSql('DROP TABLE instructor');
        $this->addSql('DROP TABLE lab');
        $this->addSql('DROP TABLE course_instance');
        $this->addSql('DROP TABLE course_instance_instructor');
        $this->addSql('DROP TABLE xyquestion');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE lab_xyquestion_response');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE lab_xyquestion_danger_zone');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE lab_xyquestion');
        $this->addSql('DROP TABLE lab_response');
        $this->addSql('DROP TABLE affective_field');
        $this->addSql('DROP TABLE enrolment');
    }
}
