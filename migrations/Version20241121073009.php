<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121073009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at column to content table with default value';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne avec une valeur par défaut
        $this->addSql("ALTER TABLE content ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)'");
        
        // Supprimer la valeur par défaut après avoir initialisé les données
        $this->addSql("ALTER TABLE content ALTER created_at DROP DEFAULT");
    }

    public function down(Schema $schema): void
    {
        // Supprimer la colonne created_at
        $this->addSql('ALTER TABLE content DROP created_at');
    }
}
