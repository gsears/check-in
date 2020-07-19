<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200719183349 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE lab_survey_response_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE lab_survey_response (id INT NOT NULL, student_id INT NOT NULL, lab_survey_id INT NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DE11B29CB944F1A ON lab_survey_response (student_id)');
        $this->addSql('CREATE INDEX IDX_DE11B293D9AB34F ON lab_survey_response (lab_survey_id)');
        $this->addSql('ALTER TABLE lab_survey_response ADD CONSTRAINT FK_DE11B29CB944F1A FOREIGN KEY (student_id) REFERENCES student (guid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_survey_response ADD CONSTRAINT FK_DE11B293D9AB34F FOREIGN KEY (lab_survey_id) REFERENCES lab_survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_survey_xyquestion ADD index INT NOT NULL');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response DROP CONSTRAINT fk_2af4ff9fcb944f1a');
        $this->addSql('DROP INDEX idx_2af4ff9fcb944f1a');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response RENAME COLUMN student_id TO lab_survey_response_id');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response ADD CONSTRAINT FK_2AF4FF9F2FE6A126 FOREIGN KEY (lab_survey_response_id) REFERENCES lab_survey_response (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2AF4FF9F2FE6A126 ON lab_survey_xyquestion_response (lab_survey_response_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response DROP CONSTRAINT FK_2AF4FF9F2FE6A126');
        $this->addSql('DROP SEQUENCE lab_survey_response_id_seq CASCADE');
        $this->addSql('DROP TABLE lab_survey_response');
        $this->addSql('DROP INDEX IDX_2AF4FF9F2FE6A126');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response RENAME COLUMN lab_survey_response_id TO student_id');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response ADD CONSTRAINT fk_2af4ff9fcb944f1a FOREIGN KEY (student_id) REFERENCES student (guid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2af4ff9fcb944f1a ON lab_survey_xyquestion_response (student_id)');
        $this->addSql('ALTER TABLE lab_survey_xyquestion DROP index');
    }
}
