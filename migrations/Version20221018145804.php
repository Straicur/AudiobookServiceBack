<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221018145804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audiobook (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, album VARCHAR(255) NOT NULL, year DATETIME NOT NULL, encoded VARCHAR(255) DEFAULT NULL, duration VARCHAR(255) NOT NULL, size VARCHAR(255) NOT NULL, parts INT NOT NULL, description LONGTEXT NOT NULL, age INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audiobook_audiobook_category (audiobook_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_category_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_9DADCFA0ED9E55A4 (audiobook_id), INDEX IDX_9DADCFA0DDFE1F68 (audiobook_category_id), PRIMARY KEY(audiobook_id, audiobook_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audiobook_category (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(50) NOT NULL, INDEX IDX_EC2A724A727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audiobook_info (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', part INT NOT NULL, ended_time VARCHAR(255) NOT NULL, watching_date DATETIME NOT NULL, INDEX IDX_738E16C9A76ED395 (user_id), INDEX IDX_738E16C9ED9E55A4 (audiobook_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE institution (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, max_admins INT NOT NULL, max_users INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE my_list (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_84EFD14CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE my_list_audiobook (my_list_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_4AF5E0DB757BAA7 (my_list_id), INDEX IDX_4AF5E0DED9E55A4 (audiobook_id), PRIMARY KEY(my_list_id, audiobook_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE register_code (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', code VARCHAR(512) NOT NULL, date_add DATETIME NOT NULL, date_accept DATETIME DEFAULT NULL, active TINYINT(1) NOT NULL, INDEX IDX_A3722038A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audiobook_audiobook_category ADD CONSTRAINT FK_9DADCFA0ED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audiobook_audiobook_category ADD CONSTRAINT FK_9DADCFA0DDFE1F68 FOREIGN KEY (audiobook_category_id) REFERENCES audiobook_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audiobook_category ADD CONSTRAINT FK_EC2A724A727ACA70 FOREIGN KEY (parent_id) REFERENCES audiobook_category (id)');
        $this->addSql('ALTER TABLE audiobook_info ADD CONSTRAINT FK_738E16C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE audiobook_info ADD CONSTRAINT FK_738E16C9ED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id)');
        $this->addSql('ALTER TABLE my_list ADD CONSTRAINT FK_84EFD14CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE my_list_audiobook ADD CONSTRAINT FK_4AF5E0DB757BAA7 FOREIGN KEY (my_list_id) REFERENCES my_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE my_list_audiobook ADD CONSTRAINT FK_4AF5E0DED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE register_code ADD CONSTRAINT FK_A3722038A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_audiobook_category DROP FOREIGN KEY FK_9DADCFA0ED9E55A4');
        $this->addSql('ALTER TABLE audiobook_info DROP FOREIGN KEY FK_738E16C9ED9E55A4');
        $this->addSql('ALTER TABLE my_list_audiobook DROP FOREIGN KEY FK_4AF5E0DED9E55A4');
        $this->addSql('ALTER TABLE audiobook_audiobook_category DROP FOREIGN KEY FK_9DADCFA0DDFE1F68');
        $this->addSql('ALTER TABLE audiobook_category DROP FOREIGN KEY FK_EC2A724A727ACA70');
        $this->addSql('ALTER TABLE my_list_audiobook DROP FOREIGN KEY FK_4AF5E0DB757BAA7');
        $this->addSql('DROP TABLE audiobook');
        $this->addSql('DROP TABLE audiobook_audiobook_category');
        $this->addSql('DROP TABLE audiobook_category');
        $this->addSql('DROP TABLE audiobook_info');
        $this->addSql('DROP TABLE institution');
        $this->addSql('DROP TABLE my_list');
        $this->addSql('DROP TABLE my_list_audiobook');
        $this->addSql('DROP TABLE register_code');
    }
}
