<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601092727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE item ALTER description DROP NOT NULL');
        $this->addSql('ALTER TABLE item RENAME COLUMN date_created TO created_at');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_1F1B251E7E3C61F9 ON item (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E7E3C61F9');
        $this->addSql('DROP INDEX IDX_1F1B251E7E3C61F9');
        $this->addSql('ALTER TABLE item DROP owner_id');
        $this->addSql('ALTER TABLE item ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE item RENAME COLUMN created_at TO date_created');
    }
}
