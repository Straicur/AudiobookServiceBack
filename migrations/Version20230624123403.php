<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230624123403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_info DROP FOREIGN KEY FK_738E16C9ED9E55A4');
        $this->addSql('ALTER TABLE audiobook_info ADD CONSTRAINT FK_738E16C9ED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audiobook_rating DROP FOREIGN KEY FK_92418CDAED9E55A4');
        $this->addSql('ALTER TABLE audiobook_rating ADD CONSTRAINT FK_92418CDAED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audiobook_user_comment DROP FOREIGN KEY FK_C1DB9EDAED9E55A4');
        $this->addSql('ALTER TABLE audiobook_user_comment ADD CONSTRAINT FK_C1DB9EDAED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audiobook_info DROP FOREIGN KEY FK_738E16C9ED9E55A4');
        $this->addSql('ALTER TABLE audiobook_info ADD CONSTRAINT FK_738E16C9ED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE audiobook_rating DROP FOREIGN KEY FK_92418CDAED9E55A4');
        $this->addSql('ALTER TABLE audiobook_rating ADD CONSTRAINT FK_92418CDAED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE audiobook_user_comment DROP FOREIGN KEY FK_C1DB9EDAED9E55A4');
        $this->addSql('ALTER TABLE audiobook_user_comment ADD CONSTRAINT FK_C1DB9EDAED9E55A4 FOREIGN KEY (audiobook_id) REFERENCES audiobook (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
