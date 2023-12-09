<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021144215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE proposed_audiobooks (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_EC0869CBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposed_audiobooks_audiobook (proposed_audiobooks_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', audiobook_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_C286000A415FE4A1 (proposed_audiobooks_id), INDEX IDX_C286000AED9E55A4 (audiobook_id), PRIMARY KEY(proposed_audiobooks_id, audiobook_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE proposed_audiobooks ADD CONSTRAINT FK_EC0869CBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE proposed_audiobooks_audiobook ADD CONSTRAINT FK_C286000A415FE4A1 FOREIGN KEY (proposed_audiobooks_id) REFERENCES proposed_audiobooks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposed_audiobooks_audiobook ADD CONSTRAINT FK_C286000AED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audiobook_category ADD active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE proposed_audiobooks_audiobook DROP FOREIGN KEY FK_C286000A415FE4A1');
        $this->addSql('DROP TABLE proposed_audiobooks');
        $this->addSql('DROP TABLE proposed_audiobooks_audiobook');
        $this->addSql('ALTER TABLE audiobook_category DROP active');
    }
}
