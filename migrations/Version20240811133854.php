<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811133854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report ADD banned_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77841D3CC070 FOREIGN KEY (banned_id) REFERENCES user_ban_history (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F77841D3CC070 ON report (banned_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77841D3CC070');
        $this->addSql('DROP INDEX UNIQ_C42F77841D3CC070 ON report');
        $this->addSql('ALTER TABLE report DROP banned_id');
    }
}
