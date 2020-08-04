<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804133956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE lab_sentiment_question_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_sentiment_question_danger_zone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sentiment_question_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lab_sentiment_question_response_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE lab_sentiment_question (id INT NOT NULL, lab_id INT NOT NULL, sentiment_question_id INT NOT NULL, index INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_622BFE68628913D5 ON lab_sentiment_question (lab_id)');
        $this->addSql('CREATE INDEX IDX_622BFE687ECD7BBB ON lab_sentiment_question (sentiment_question_id)');
        $this->addSql('CREATE TABLE lab_sentiment_question_danger_zone (id INT NOT NULL, lab_sentiment_question_id INT NOT NULL, risk_level INT NOT NULL, classification VARCHAR(255) NOT NULL, confidence_min DOUBLE PRECISION NOT NULL, confidence_max DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_31D6F5626066AB9 ON lab_sentiment_question_danger_zone (lab_sentiment_question_id)');
        $this->addSql('CREATE TABLE sentiment_question (id INT NOT NULL, name VARCHAR(255) NOT NULL, question_text VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE lab_sentiment_question_response (id INT NOT NULL, lab_sentiment_question_id INT NOT NULL, lab_response_id INT NOT NULL, text TEXT NOT NULL, classification VARCHAR(255) NOT NULL, confidence DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9567A8686066AB9 ON lab_sentiment_question_response (lab_sentiment_question_id)');
        $this->addSql('CREATE INDEX IDX_9567A86871FEB27B ON lab_sentiment_question_response (lab_response_id)');
        $this->addSql('ALTER TABLE lab_sentiment_question ADD CONSTRAINT FK_622BFE68628913D5 FOREIGN KEY (lab_id) REFERENCES lab (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_sentiment_question ADD CONSTRAINT FK_622BFE687ECD7BBB FOREIGN KEY (sentiment_question_id) REFERENCES sentiment_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_sentiment_question_danger_zone ADD CONSTRAINT FK_31D6F5626066AB9 FOREIGN KEY (lab_sentiment_question_id) REFERENCES lab_sentiment_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_sentiment_question_response ADD CONSTRAINT FK_9567A8686066AB9 FOREIGN KEY (lab_sentiment_question_id) REFERENCES lab_sentiment_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lab_sentiment_question_response ADD CONSTRAINT FK_9567A86871FEB27B FOREIGN KEY (lab_response_id) REFERENCES lab_response (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lab_sentiment_question_danger_zone DROP CONSTRAINT FK_31D6F5626066AB9');
        $this->addSql('ALTER TABLE lab_sentiment_question_response DROP CONSTRAINT FK_9567A8686066AB9');
        $this->addSql('ALTER TABLE lab_sentiment_question DROP CONSTRAINT FK_622BFE687ECD7BBB');
        $this->addSql('DROP SEQUENCE lab_sentiment_question_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_sentiment_question_danger_zone_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sentiment_question_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lab_sentiment_question_response_id_seq CASCADE');
        $this->addSql('DROP TABLE lab_sentiment_question');
        $this->addSql('DROP TABLE lab_sentiment_question_danger_zone');
        $this->addSql('DROP TABLE sentiment_question');
        $this->addSql('DROP TABLE lab_sentiment_question_response');
    }
}
