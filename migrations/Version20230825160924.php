<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230825160924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_category DROP FOREIGN KEY FK_EC2A724A727ACA70');
        $this->addSql('ALTER TABLE audiobook_category ADD CONSTRAINT FK_EC2A724A727ACA70 FOREIGN KEY (parent_id) REFERENCES audiobook_category (id)');
        $this->addSql('ALTER TABLE notification DROP date_deleted');
        $this->addSql('ALTER TABLE notification_check DROP FOREIGN KEY FK_FA3A49EAEF1A9D84');
        $this->addSql('ALTER TABLE notification_check ADD CONSTRAINT FK_FA3A49EAEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_category DROP FOREIGN KEY FK_EC2A724A727ACA70');
        $this->addSql('ALTER TABLE audiobook_category ADD CONSTRAINT FK_EC2A724A727ACA70 FOREIGN KEY (parent_id) REFERENCES audiobook_category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD date_deleted DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_check DROP FOREIGN KEY FK_FA3A49EAEF1A9D84');
        $this->addSql('ALTER TABLE notification_check ADD CONSTRAINT FK_FA3A49EAEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
