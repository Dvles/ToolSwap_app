<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919100058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool ADD tool_calendar_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED19072BC96 FOREIGN KEY (tool_calendar_id) REFERENCES tool_calendar (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20F33ED19072BC96 ON tool (tool_calendar_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED19072BC96');
        $this->addSql('DROP INDEX UNIQ_20F33ED19072BC96 ON tool');
        $this->addSql('ALTER TABLE tool DROP tool_calendar_id');
    }
}
