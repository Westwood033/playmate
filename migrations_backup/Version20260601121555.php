<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601121555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournament ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D97E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_BD5FB8D97E3C61F9 ON tournament (owner_id)');
        $this->addSql('ALTER TABLE "user" DROP shop_name');
        $this->addSql('ALTER TABLE "user" DROP shop_address');
        $this->addSql('ALTER TABLE "user" DROP phone');
        $this->addSql('ALTER TABLE "user" DROP shop_request');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournament DROP CONSTRAINT FK_BD5FB8D97E3C61F9');
        $this->addSql('DROP INDEX IDX_BD5FB8D97E3C61F9');
        $this->addSql('ALTER TABLE tournament DROP owner_id');
        $this->addSql('ALTER TABLE "user" ADD shop_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD shop_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD phone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD shop_request BOOLEAN DEFAULT NULL');
    }
}
