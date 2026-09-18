<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260918112024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: affiliate, venue, category, event, event_category, area, price';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE affiliate (name VARCHAR(255) NOT NULL, logoUrl VARCHAR(512) DEFAULT NULL, id BINARY(16) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE area (name VARCHAR(255) NOT NULL, capacity INT NOT NULL, reservedQty INT NOT NULL, soldQty INT NOT NULL, id BINARY(16) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, event_id BINARY(16) NOT NULL, INDEX IDX_D7943D6871F7E88B (event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (name VARCHAR(255) NOT NULL, id BINARY(16) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event (title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, priceInfo LONGTEXT DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, salesEnd DATETIME NOT NULL, doorsOpen DATETIME DEFAULT NULL, doorsClose DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, eventType VARCHAR(20) NOT NULL, imageId VARCHAR(255) DEFAULT NULL, imageCopyright VARCHAR(255) DEFAULT NULL, id BINARY(16) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, venue_id BINARY(16) NOT NULL, affiliate_id BINARY(16) NOT NULL, INDEX IDX_3BAE0AA740A73EBA (venue_id), INDEX IDX_3BAE0AA79F12C49A (affiliate_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_category (event_id BINARY(16) NOT NULL, category_id BINARY(16) NOT NULL, INDEX IDX_40A0F01171F7E88B (event_id), INDEX IDX_40A0F01112469DE2 (category_id), PRIMARY KEY (event_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE price (name VARCHAR(255) NOT NULL, basePriceCents INT NOT NULL, ticketFeeCents INT NOT NULL, outletFeeCents INT NOT NULL, currency VARCHAR(3) NOT NULL, id BINARY(16) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, area_id BINARY(16) NOT NULL, INDEX IDX_CAC822D9BD0F409C (area_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE venue (name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, zipCode VARCHAR(20) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(2) NOT NULL, latitude NUMERIC(10, 7) NOT NULL, longitude NUMERIC(10, 7) NOT NULL, id BINARY(16) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D6871F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA740A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA79F12C49A FOREIGN KEY (affiliate_id) REFERENCES affiliate (id)');
        $this->addSql('ALTER TABLE event_category ADD CONSTRAINT FK_40A0F01171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_category ADD CONSTRAINT FK_40A0F01112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE area DROP FOREIGN KEY FK_D7943D6871F7E88B');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA740A73EBA');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA79F12C49A');
        $this->addSql('ALTER TABLE event_category DROP FOREIGN KEY FK_40A0F01171F7E88B');
        $this->addSql('ALTER TABLE event_category DROP FOREIGN KEY FK_40A0F01112469DE2');
        $this->addSql('ALTER TABLE price DROP FOREIGN KEY FK_CAC822D9BD0F409C');
        $this->addSql('DROP TABLE affiliate');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_category');
        $this->addSql('DROP TABLE price');
        $this->addSql('DROP TABLE venue');
    }
}
