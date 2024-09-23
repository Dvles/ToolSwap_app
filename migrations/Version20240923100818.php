<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240923100818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lender_review ADD user_leaving_review_id INT NOT NULL, ADD user_being_reviewed_id INT NOT NULL');
        $this->addSql('ALTER TABLE lender_review ADD CONSTRAINT FK_14FFCC36DCAFF04B FOREIGN KEY (user_leaving_review_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE lender_review ADD CONSTRAINT FK_14FFCC36F5F6B329 FOREIGN KEY (user_being_reviewed_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_14FFCC36DCAFF04B ON lender_review (user_leaving_review_id)');
        $this->addSql('CREATE INDEX IDX_14FFCC36F5F6B329 ON lender_review (user_being_reviewed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lender_review DROP FOREIGN KEY FK_14FFCC36DCAFF04B');
        $this->addSql('ALTER TABLE lender_review DROP FOREIGN KEY FK_14FFCC36F5F6B329');
        $this->addSql('DROP INDEX IDX_14FFCC36DCAFF04B ON lender_review');
        $this->addSql('DROP INDEX IDX_14FFCC36F5F6B329 ON lender_review');
        $this->addSql('ALTER TABLE lender_review DROP user_leaving_review_id, DROP user_being_reviewed_id');
    }
}
