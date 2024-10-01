<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241001113925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool_availability ADD user_id INT NOT NULL, ADD tool_id INT NOT NULL');
        $this->addSql('ALTER TABLE tool_availability ADD CONSTRAINT FK_B3FEEC52A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool_availability ADD CONSTRAINT FK_B3FEEC528F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id)');
        $this->addSql('CREATE INDEX IDX_B3FEEC52A76ED395 ON tool_availability (user_id)');
        $this->addSql('CREATE INDEX IDX_B3FEEC528F7B22CC ON tool_availability (tool_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool_availability DROP FOREIGN KEY FK_B3FEEC52A76ED395');
        $this->addSql('ALTER TABLE tool_availability DROP FOREIGN KEY FK_B3FEEC528F7B22CC');
        $this->addSql('DROP INDEX IDX_B3FEEC52A76ED395 ON tool_availability');
        $this->addSql('DROP INDEX IDX_B3FEEC528F7B22CC ON tool_availability');
        $this->addSql('ALTER TABLE tool_availability DROP user_id, DROP tool_id');
    }
}
