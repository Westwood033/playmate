<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511142919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournament_user DROP CONSTRAINT fk_ba1e647733d1a3e7');
        $this->addSql('ALTER TABLE tournament_user DROP CONSTRAINT fk_ba1e6477a76ed395');
        $this->addSql('DROP TABLE tournament_user');
        $this->addSql('ALTER TABLE "user" ADD shop_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD shop_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD phone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD shop_request BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tournament_user (tournament_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (tournament_id, user_id))');
        $this->addSql('CREATE INDEX idx_ba1e6477a76ed395 ON tournament_user (user_id)');
        $this->addSql('CREATE INDEX idx_ba1e647733d1a3e7 ON tournament_user (tournament_id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT fk_ba1e647733d1a3e7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT fk_ba1e6477a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP shop_name');
        $this->addSql('ALTER TABLE "user" DROP shop_address');
        $this->addSql('ALTER TABLE "user" DROP phone');
        $this->addSql('ALTER TABLE "user" DROP shop_request');
    }
}
