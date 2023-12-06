<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230624123219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_user_comment_like DROP FOREIGN KEY FK_3CA1FD19189CD9DF');
        $this->addSql('ALTER TABLE audiobook_user_comment_like ADD CONSTRAINT FK_3CA1FD19189CD9DF FOREIGN KEY (audiobook_user_comment_id) REFERENCES audiobook_user_comment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_user_comment_like DROP FOREIGN KEY FK_3CA1FD19189CD9DF');
        $this->addSql('ALTER TABLE audiobook_user_comment_like ADD CONSTRAINT FK_3CA1FD19189CD9DF FOREIGN KEY (audiobook_user_comment_id) REFERENCES audiobook_user_comment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
