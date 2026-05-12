<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512131031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tournament_user (tournament_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (tournament_id, user_id))');
        $this->addSql('CREATE INDEX IDX_BA1E647733D1A3E7 ON tournament_user (tournament_id)');
        $this->addSql('CREATE INDEX IDX_BA1E6477A76ED395 ON tournament_user (user_id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E647733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E6477A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D97E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_BD5FB8D97E3C61F9 ON tournament (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournament_user DROP CONSTRAINT FK_BA1E647733D1A3E7');
        $this->addSql('ALTER TABLE tournament_user DROP CONSTRAINT FK_BA1E6477A76ED395');
        $this->addSql('DROP TABLE tournament_user');
        $this->addSql('ALTER TABLE tournament DROP CONSTRAINT FK_BD5FB8D97E3C61F9');
        $this->addSql('DROP INDEX IDX_BD5FB8D97E3C61F9');
        $this->addSql('ALTER TABLE tournament DROP owner_id');
    }
}
