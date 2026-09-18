<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260918184348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cart data model: cart, ticket_reservation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cart (expires_at DATETIME NOT NULL, id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, affiliate_id BINARY(16) NOT NULL, INDEX IDX_BA388B79F12C49A (affiliate_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ticket_reservation (qty INT NOT NULL, id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, cart_id BINARY(16) NOT NULL, price_id BINARY(16) NOT NULL, INDEX IDX_2E584F2A1AD5CDBF (cart_id), INDEX IDX_2E584F2AD614C7E7 (price_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B79F12C49A FOREIGN KEY (affiliate_id) REFERENCES affiliate (id)');
        $this->addSql('ALTER TABLE ticket_reservation ADD CONSTRAINT FK_2E584F2A1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE ticket_reservation ADD CONSTRAINT FK_2E584F2AD614C7E7 FOREIGN KEY (price_id) REFERENCES price (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B79F12C49A');
        $this->addSql('ALTER TABLE ticket_reservation DROP FOREIGN KEY FK_2E584F2A1AD5CDBF');
        $this->addSql('ALTER TABLE ticket_reservation DROP FOREIGN KEY FK_2E584F2AD614C7E7');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE ticket_reservation');
    }
}
