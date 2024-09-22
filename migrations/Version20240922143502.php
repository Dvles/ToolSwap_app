<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922143502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE borrow_tool (id INT AUTO_INCREMENT NOT NULL, user_borrower_id INT NOT NULL, tool_being_borrowed_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_A3DE04F4B8D5F8BE (user_borrower_id), INDEX IDX_A3DE04F42F823257 (tool_being_borrowed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE homepage (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool (id INT AUTO_INCREMENT NOT NULL, user_of_tool_id INT NOT NULL, tool_calendar_id INT DEFAULT NULL, tool_category_id INT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, tool_condition VARCHAR(20) NOT NULL, availability TINYINT(1) DEFAULT NULL, price_day NUMERIC(5, 2) DEFAULT NULL, image_tool VARCHAR(255) NOT NULL, INDEX IDX_20F33ED1D5691591 (user_of_tool_id), UNIQUE INDEX UNIQ_20F33ED19072BC96 (tool_calendar_id), INDEX IDX_20F33ED1887483BC (tool_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_calendar (id INT AUTO_INCREMENT NOT NULL, available_dates LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool_review (id INT AUTO_INCREMENT NOT NULL, user_of_review_id INT NOT NULL, tool_of_review_id INT NOT NULL, rating INT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_1C128DDA89864A9F (user_of_review_id), INDEX IDX_1C128DDA85037870 (tool_of_review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(150) NOT NULL, last_name VARCHAR(150) NOT NULL, phone_number VARCHAR(15) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, community VARCHAR(255) NOT NULL, rewards INT DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE borrow_tool ADD CONSTRAINT FK_A3DE04F4B8D5F8BE FOREIGN KEY (user_borrower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE borrow_tool ADD CONSTRAINT FK_A3DE04F42F823257 FOREIGN KEY (tool_being_borrowed_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1D5691591 FOREIGN KEY (user_of_tool_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED19072BC96 FOREIGN KEY (tool_calendar_id) REFERENCES tool_calendar (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1887483BC FOREIGN KEY (tool_category_id) REFERENCES tool_category (id)');
        $this->addSql('ALTER TABLE tool_review ADD CONSTRAINT FK_1C128DDA89864A9F FOREIGN KEY (user_of_review_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tool_review ADD CONSTRAINT FK_1C128DDA85037870 FOREIGN KEY (tool_of_review_id) REFERENCES tool (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrow_tool DROP FOREIGN KEY FK_A3DE04F4B8D5F8BE');
        $this->addSql('ALTER TABLE borrow_tool DROP FOREIGN KEY FK_A3DE04F42F823257');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1D5691591');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED19072BC96');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1887483BC');
        $this->addSql('ALTER TABLE tool_review DROP FOREIGN KEY FK_1C128DDA89864A9F');
        $this->addSql('ALTER TABLE tool_review DROP FOREIGN KEY FK_1C128DDA85037870');
        $this->addSql('DROP TABLE borrow_tool');
        $this->addSql('DROP TABLE homepage');
        $this->addSql('DROP TABLE tool');
        $this->addSql('DROP TABLE tool_calendar');
        $this->addSql('DROP TABLE tool_category');
        $this->addSql('DROP TABLE tool_review');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
