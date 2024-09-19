<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919093126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool ADD user_of_tool_id INT NOT NULL');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1D5691591 FOREIGN KEY (user_of_tool_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_20F33ED1D5691591 ON tool (user_of_tool_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1D5691591');
        $this->addSql('DROP INDEX IDX_20F33ED1D5691591 ON tool');
        $this->addSql('ALTER TABLE tool DROP user_of_tool_id');
    }
}
