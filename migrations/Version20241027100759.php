<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027100759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
        DROP PROCEDURE IF EXISTS calculate_audiobooks_rating;
        
        CREATE PROCEDURE calculate_audiobooks_rating()
        BEGIN
        CREATE TEMPORARY TABLE IF NOT EXISTS audiobooksToUpdate
        (
            Id binary(16)
        ) AS
        SELECT Id
        FROM audiobook
        where active = true;
        
        UPDATE audiobook a
        set a.avg_rating = (SELECT IF(COUNT(*) > 0,
                                      (SUM(rating) / COUNT(*)),
                                      0) AS average_rating
                            FROM audiobook_rating
                            where
                                audiobook_rating.audiobook_id =
                                a.id)
        Where a.Id IN (SELECT Id FROM audiobooksToUpdate);
        END');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP PROCEDURE IF EXISTS calculate_audiobooks_rating');
    }
}
