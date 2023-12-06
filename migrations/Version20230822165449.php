<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230822165449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_check DROP FOREIGN KEY FK_FA3A49EAEF1A9D84');
        $this->addSql('ALTER TABLE notification_check ADD CONSTRAINT FK_FA3A49EAEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_check DROP FOREIGN KEY FK_FA3A49EAEF1A9D84');
        $this->addSql('ALTER TABLE notification_check ADD CONSTRAINT FK_FA3A49EAEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
