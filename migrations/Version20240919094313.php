<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919094313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrow_tool ADD user_borrower_id INT NOT NULL');
        $this->addSql('ALTER TABLE borrow_tool ADD CONSTRAINT FK_A3DE04F4B8D5F8BE FOREIGN KEY (user_borrower_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A3DE04F4B8D5F8BE ON borrow_tool (user_borrower_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrow_tool DROP FOREIGN KEY FK_A3DE04F4B8D5F8BE');
        $this->addSql('DROP INDEX IDX_A3DE04F4B8D5F8BE ON borrow_tool');
        $this->addSql('ALTER TABLE borrow_tool DROP user_borrower_id');
    }
}
