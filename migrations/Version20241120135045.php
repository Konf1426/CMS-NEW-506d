<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120135045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9F675F31B');
        $this->addSql('DROP INDEX IDX_FEC530A9F675F31B ON content');
        $this->addSql('ALTER TABLE content ADD author_uuid BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP author_id');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A93590D879 FOREIGN KEY (author_uuid) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FEC530A93590D879 ON content (author_uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A93590D879');
        $this->addSql('DROP INDEX IDX_FEC530A93590D879 ON content');
        $this->addSql('ALTER TABLE content ADD author_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', DROP author_uuid');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_FEC530A9F675F31B ON content (author_id)');
    }
}
