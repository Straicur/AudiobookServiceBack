<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221207155825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_user_comment_like ADD user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE audiobook_user_comment_like ADD CONSTRAINT FK_3CA1FD19A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3CA1FD19A76ED395 ON audiobook_user_comment_like (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_user_comment_like DROP FOREIGN KEY FK_3CA1FD19A76ED395');
        $this->addSql('DROP INDEX IDX_3CA1FD19A76ED395 ON audiobook_user_comment_like');
        $this->addSql('ALTER TABLE audiobook_user_comment_like DROP user_id');
    }
}
