<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221203131222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audiobook_rating (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', rating TINYINT(1) NOT NULL, INDEX IDX_92418CDAED9E55A4 (audiobook_id), INDEX IDX_92418CDAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audiobook_rating ADD CONSTRAINT FK_92418CDAED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id)');
        $this->addSql('ALTER TABLE audiobook_rating ADD CONSTRAINT FK_92418CDAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE audiobook_rating');
    }
}
