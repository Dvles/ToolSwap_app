<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003070915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE borrow_tool (id INT AUTO_INCREMENT NOT NULL, user_borrower_id INT NOT NULL, tool_being_borrowed_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_A3DE04F4B8D5F8BE (user_borrower_id), INDEX IDX_A3DE04F42F823257 (tool_being_borrowed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lender_review (id INT AUTO_INCREMENT NOT NULL, user_leaving_review_id INT NOT NULL, user_being_reviewed_id INT NOT NULL, rating INT NOT NULL, comments LONGTEXT DEFAULT NULL, INDEX IDX_14FFCC36DCAFF04B (user_leaving_review_id), INDEX IDX_14FFCC36F5F6B329 (user_being_reviewed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, tool_category_id INT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, tool_condition VARCHAR(20) NOT NULL, availability TINYINT(1) DEFAULT NULL, price_day NUMERIC(5, 2) DEFAULT NULL, image_tool VARCHAR(255) NOT NULL, INDEX IDX_20F33ED17E3C61F9 (owner_id), INDEX IDX_20F33ED1887483BC (tool_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_availability (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, tool_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, start DATE NOT NULL, end DATE NOT NULL, description LONGTEXT DEFAULT NULL, background_color VARCHAR(255) NOT NULL, border_color VARCHAR(255) NOT NULL, text_color VARCHAR(255) NOT NULL, INDEX IDX_B3FEEC52A76ED395 (user_id), INDEX IDX_B3FEEC528F7B22CC (tool_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_review (id INT AUTO_INCREMENT NOT NULL, user_of_review_id INT NOT NULL, tool_of_review_id INT NOT NULL, rating INT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_1C128DDA89864A9F (user_of_review_id), INDEX IDX_1C128DDA85037870 (tool_of_review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(150) NOT NULL, last_name VARCHAR(150) NOT NULL, phone_number VARCHAR(15) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, community VARCHAR(255) NOT NULL, rewards INT DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE borrow_tool ADD CONSTRAINT FK_A3DE04F4B8D5F8BE FOREIGN KEY (user_borrower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE borrow_tool ADD CONSTRAINT FK_A3DE04F42F823257 FOREIGN KEY (tool_being_borrowed_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE lender_review ADD CONSTRAINT FK_14FFCC36DCAFF04B FOREIGN KEY (user_leaving_review_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE lender_review ADD CONSTRAINT FK_14FFCC36F5F6B329 FOREIGN KEY (user_being_reviewed_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED17E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1887483BC FOREIGN KEY (tool_category_id) REFERENCES tool_category (id)');
        $this->addSql('ALTER TABLE tool_availability ADD CONSTRAINT FK_B3FEEC52A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool_availability ADD CONSTRAINT FK_B3FEEC528F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE tool_review ADD CONSTRAINT FK_1C128DDA89864A9F FOREIGN KEY (user_of_review_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool_review ADD CONSTRAINT FK_1C128DDA85037870 FOREIGN KEY (tool_of_review_id) REFERENCES tool (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrow_tool DROP FOREIGN KEY FK_A3DE04F4B8D5F8BE');
        $this->addSql('ALTER TABLE borrow_tool DROP FOREIGN KEY FK_A3DE04F42F823257');
        $this->addSql('ALTER TABLE lender_review DROP FOREIGN KEY FK_14FFCC36DCAFF04B');
        $this->addSql('ALTER TABLE lender_review DROP FOREIGN KEY FK_14FFCC36F5F6B329');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED17E3C61F9');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1887483BC');
        $this->addSql('ALTER TABLE tool_availability DROP FOREIGN KEY FK_B3FEEC52A76ED395');
        $this->addSql('ALTER TABLE tool_availability DROP FOREIGN KEY FK_B3FEEC528F7B22CC');
        $this->addSql('ALTER TABLE tool_review DROP FOREIGN KEY FK_1C128DDA89864A9F');
        $this->addSql('ALTER TABLE tool_review DROP FOREIGN KEY FK_1C128DDA85037870');
        $this->addSql('DROP TABLE borrow_tool');
        $this->addSql('DROP TABLE lender_review');
        $this->addSql('DROP TABLE tool');
        $this->addSql('DROP TABLE tool_availability');
        $this->addSql('DROP TABLE tool_category');
        $this->addSql('DROP TABLE tool_review');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
