<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919101441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool_review ADD tool_of_review_id INT NOT NULL');
        $this->addSql('ALTER TABLE tool_review ADD CONSTRAINT FK_1C128DDA85037870 FOREIGN KEY (tool_of_review_id) REFERENCES tool (id)');
        $this->addSql('CREATE INDEX IDX_1C128DDA85037870 ON tool_review (tool_of_review_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool_review DROP FOREIGN KEY FK_1C128DDA85037870');
        $this->addSql('DROP INDEX IDX_1C128DDA85037870 ON tool_review');
        $this->addSql('ALTER TABLE tool_review DROP tool_of_review_id');
    }
}
