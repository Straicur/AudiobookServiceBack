<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221112072742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authentication_token DROP FOREIGN KEY FK_B54C4ADDA76ED395');
        $this->addSql('ALTER TABLE authentication_token ADD CONSTRAINT FK_B54C4ADDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE my_list DROP FOREIGN KEY FK_84EFD14CA76ED395');
        $this->addSql('ALTER TABLE my_list ADD CONSTRAINT FK_84EFD14CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposed_audiobooks DROP FOREIGN KEY FK_EC0869CBA76ED395');
        $this->addSql('ALTER TABLE proposed_audiobooks ADD CONSTRAINT FK_EC0869CBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_information DROP FOREIGN KEY FK_8062D116A76ED395');
        $this->addSql('ALTER TABLE user_information ADD CONSTRAINT FK_8062D116A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_password DROP FOREIGN KEY FK_D54FA2D5A76ED395');
        $this->addSql('ALTER TABLE user_password ADD CONSTRAINT FK_D54FA2D5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_settings DROP FOREIGN KEY FK_5C844C5A76ED395');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT FK_5C844C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authentication_token DROP FOREIGN KEY FK_B54C4ADDA76ED395');
        $this->addSql('ALTER TABLE authentication_token ADD CONSTRAINT FK_B54C4ADDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE my_list DROP FOREIGN KEY FK_84EFD14CA76ED395');
        $this->addSql('ALTER TABLE my_list ADD CONSTRAINT FK_84EFD14CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE proposed_audiobooks DROP FOREIGN KEY FK_EC0869CBA76ED395');
        $this->addSql('ALTER TABLE proposed_audiobooks ADD CONSTRAINT FK_EC0869CBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_information DROP FOREIGN KEY FK_8062D116A76ED395');
        $this->addSql('ALTER TABLE user_information ADD CONSTRAINT FK_8062D116A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_password DROP FOREIGN KEY FK_D54FA2D5A76ED395');
        $this->addSql('ALTER TABLE user_password ADD CONSTRAINT FK_D54FA2D5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_settings DROP FOREIGN KEY FK_5C844C5A76ED395');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT FK_5C844C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
