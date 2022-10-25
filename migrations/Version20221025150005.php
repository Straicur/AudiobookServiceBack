<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221025150005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_category DROP FOREIGN KEY FK_EC2A724A727ACA70');
        $this->addSql('DROP INDEX IDX_EC2A724A727ACA70 ON audiobook_category');
        $this->addSql('ALTER TABLE audiobook_category DROP parent_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_category ADD parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE audiobook_category ADD CONSTRAINT FK_EC2A724A727ACA70 FOREIGN KEY (parent_id) REFERENCES audiobook_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_EC2A724A727ACA70 ON audiobook_category (parent_id)');
    }
}
