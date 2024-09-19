<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919100813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool ADD tool_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1887483BC FOREIGN KEY (tool_category_id) REFERENCES tool_category (id)');
        $this->addSql('CREATE INDEX IDX_20F33ED1887483BC ON tool (tool_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1887483BC');
        $this->addSql('DROP INDEX IDX_20F33ED1887483BC ON tool');
        $this->addSql('ALTER TABLE tool DROP tool_category_id');
    }
}
