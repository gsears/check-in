<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716194801 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE xyquestion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_survey_xyquestion_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_survey_xyquestion_response_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE affective_field_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE xyquestion (id INT NOT NULL, x_field_id INT NOT NULL, y_field_id INT NOT NULL, name VARCHAR(255) NOT NULL, question_text VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_89C14EB38AEAFB64 ON xyquestion (x_field_id)');
        $this->addSql('CREATE INDEX IDX_89C14EB36528905A ON xyquestion (y_field_id)');
        $this->addSql('CREATE TABLE lab_survey_xyquestion (id INT NOT NULL, lab_survey_id INT NOT NULL, xy_question_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2778CAD03D9AB34F ON lab_survey_xyquestion (lab_survey_id)');
        $this->addSql('CREATE INDEX IDX_2778CAD0EA320F6 ON lab_survey_xyquestion (xy_question_id)');
        $this->addSql('CREATE TABLE lab_survey (id INT NOT NULL, course_instance_id INT NOT NULL, lab_name VARCHAR(255) NOT NULL, start_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_38871DA84E3F42C9 ON lab_survey (course_instance_id)');
        $this->addSql('COMMENT ON COLUMN lab_survey.start_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE lab_survey_xyquestion_response (id INT NOT NULL, student_id INT NOT NULL, x_value INT NOT NULL, y_value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2AF4FF9FCB944F1A ON lab_survey_xyquestion_response (student_id)');
        $this->addSql('CREATE TABLE affective_field (id INT NOT NULL, name VARCHAR(255) NOT NULL, low_label VARCHAR(255) NOT NULL, high_label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE xyquestion ADD CONSTRAINT FK_89C14EB38AEAFB64 FOREIGN KEY (x_field_id) REFERENCES affective_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyquestion ADD CONSTRAINT FK_89C14EB36528905A FOREIGN KEY (y_field_id) REFERENCES affective_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_survey_xyquestion ADD CONSTRAINT FK_2778CAD03D9AB34F FOREIGN KEY (lab_survey_id) REFERENCES lab_survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_survey_xyquestion ADD CONSTRAINT FK_2778CAD0EA320F6 FOREIGN KEY (xy_question_id) REFERENCES xyquestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_survey ADD CONSTRAINT FK_38871DA84E3F42C9 FOREIGN KEY (course_instance_id) REFERENCES course_instance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response ADD CONSTRAINT FK_2AF4FF9FCB944F1A FOREIGN KEY (student_id) REFERENCES student (guid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lab_survey_xyquestion DROP CONSTRAINT FK_2778CAD0EA320F6');
        $this->addSql('ALTER TABLE lab_survey_xyquestion DROP CONSTRAINT FK_2778CAD03D9AB34F');
        $this->addSql('ALTER TABLE xyquestion DROP CONSTRAINT FK_89C14EB38AEAFB64');
        $this->addSql('ALTER TABLE xyquestion DROP CONSTRAINT FK_89C14EB36528905A');
        $this->addSql('DROP SEQUENCE xyquestion_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_survey_xyquestion_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_survey_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_survey_xyquestion_response_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE affective_field_id_seq CASCADE');
        $this->addSql('DROP TABLE xyquestion');
        $this->addSql('DROP TABLE lab_survey_xyquestion');
        $this->addSql('DROP TABLE lab_survey');
        $this->addSql('DROP TABLE lab_survey_xyquestion_response');
        $this->addSql('DROP TABLE affective_field');
    }
}
