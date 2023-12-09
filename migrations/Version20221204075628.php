<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221204075628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audiobook_user_comment (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', comment VARCHAR(1000) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_C1DB9EDAED9E55A4 (audiobook_id), INDEX IDX_C1DB9EDAA76ED395 (user_id), INDEX IDX_C1DB9EDA727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audiobook_user_comment ADD CONSTRAINT FK_C1DB9EDAED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id)');
        $this->addSql('ALTER TABLE audiobook_user_comment ADD CONSTRAINT FK_C1DB9EDAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE audiobook_user_comment ADD CONSTRAINT FK_C1DB9EDA727ACA70 FOREIGN KEY (parent_id) REFERENCES audiobook_user_comment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_user_comment DROP FOREIGN KEY FK_C1DB9EDA727ACA70');
        $this->addSql('DROP TABLE audiobook_user_comment');
    }
}
