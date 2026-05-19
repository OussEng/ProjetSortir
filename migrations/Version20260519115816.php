<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519115816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, zipcode VARCHAR(5) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date_time_start DATETIME NOT NULL, date_limit_registration DATETIME NOT NULL, duration VARCHAR(255) DEFAULT NULL, nb_participants INT NOT NULL, event_description LONGTEXT NOT NULL, state VARCHAR(255) NOT NULL, organiser_id INT DEFAULT NULL, site_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_3BAE0AA7A0631C12 (organiser_id), INDEX IDX_3BAE0AA7F6BD1646 (site_id), INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_participant (event_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_7C16B89171F7E88B (event_id), INDEX IDX_7C16B8919D1C3019 (participant_id), PRIMARY KEY (event_id, participant_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, city_id INT NOT NULL, INDEX IDX_5E9E89CB8BAC62AF (city_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, active TINYINT NOT NULL, phone VARCHAR(20) NOT NULL, img VARCHAR(255) DEFAULT NULL, site_id INT NOT NULL, INDEX IDX_D79F6B11F6BD1646 (site_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A0631C12 FOREIGN KEY (organiser_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE event_participant ADD CONSTRAINT FK_7C16B89171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_participant ADD CONSTRAINT FK_7C16B8919D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A0631C12');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F6BD1646');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E');
        $this->addSql('ALTER TABLE event_participant DROP FOREIGN KEY FK_7C16B89171F7E88B');
        $this->addSql('ALTER TABLE event_participant DROP FOREIGN KEY FK_7C16B8919D1C3019');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB8BAC62AF');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11F6BD1646');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_participant');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
