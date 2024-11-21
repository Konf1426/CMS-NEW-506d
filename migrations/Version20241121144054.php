<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121144054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content ADD image_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP cover_image');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A93DA5256D FOREIGN KEY (image_id) REFERENCES upload (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FEC530A93DA5256D ON content (image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A93DA5256D');
        $this->addSql('DROP INDEX IDX_FEC530A93DA5256D ON content');
        $this->addSql('ALTER TABLE content ADD cover_image VARCHAR(255) DEFAULT NULL, DROP image_id');
    }
}
