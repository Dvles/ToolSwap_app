<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919094101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool_review ADD user_of_review_id INT NOT NULL');
        $this->addSql('ALTER TABLE tool_review ADD CONSTRAINT FK_1C128DDA89864A9F FOREIGN KEY (user_of_review_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1C128DDA89864A9F ON tool_review (user_of_review_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tool_review DROP FOREIGN KEY FK_1C128DDA89864A9F');
        $this->addSql('DROP INDEX IDX_1C128DDA89864A9F ON tool_review');
        $this->addSql('ALTER TABLE tool_review DROP user_of_review_id');
    }
}
