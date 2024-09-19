<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919095254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrow_tool ADD tool_being_borrowed_id INT NOT NULL');
        $this->addSql('ALTER TABLE borrow_tool ADD CONSTRAINT FK_A3DE04F42F823257 FOREIGN KEY (tool_being_borrowed_id) REFERENCES tool (id)');
        $this->addSql('CREATE INDEX IDX_A3DE04F42F823257 ON borrow_tool (tool_being_borrowed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrow_tool DROP FOREIGN KEY FK_A3DE04F42F823257');
        $this->addSql('DROP INDEX IDX_A3DE04F42F823257 ON borrow_tool');
        $this->addSql('ALTER TABLE borrow_tool DROP tool_being_borrowed_id');
    }
}
