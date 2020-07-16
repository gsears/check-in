<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716220346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE lab_survey_xyquestion_response ADD lab_survey_xyquestion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response ADD CONSTRAINT FK_2AF4FF9F840336ED FOREIGN KEY (lab_survey_xyquestion_id) REFERENCES lab_survey_xyquestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2AF4FF9F840336ED ON lab_survey_xyquestion_response (lab_survey_xyquestion_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response DROP CONSTRAINT FK_2AF4FF9F840336ED');
        $this->addSql('DROP INDEX IDX_2AF4FF9F840336ED');
        $this->addSql('ALTER TABLE lab_survey_xyquestion_response DROP lab_survey_xyquestion_id');
    }
}
