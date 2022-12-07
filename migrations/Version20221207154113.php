<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221207154113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audiobook_user_comment_like (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_user_comment_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', liked TINYINT(1) NOT NULL, date_add DATETIME NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_3CA1FD19189CD9DF (audiobook_user_comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audiobook_user_comment_like ADD CONSTRAINT FK_3CA1FD19189CD9DF FOREIGN KEY (audiobook_user_comment_id) REFERENCES audiobook_user_comment (id)');
        $this->addSql('ALTER TABLE audiobook_user_comment ADD date_add DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE audiobook_user_comment_like');
        $this->addSql('ALTER TABLE audiobook_user_comment DROP date_add');
    }
}
