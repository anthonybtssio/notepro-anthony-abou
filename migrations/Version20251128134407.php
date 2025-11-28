<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128134407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout et initialisation de la colonne date_affichage dans evaluation.';
    }

    public function up(Schema $schema): void
    {
        // cette migration up() est auto-générée, veuillez la modifier selon vos besoins
        // L'instruction suivante met à jour la colonne (ou l'ajoute/renomme)
        $this->addSql('ALTER TABLE evaluation CHANGE date_affiche date_affichage DATETIME DEFAULT NULL');

        // COMPLÉMENT TECHNIQUE : Met à jour les données existantes avec la date de l'évaluation
        // Cela rend toutes les anciennes notes immédiatement visibles par défaut.
        $this->addSql('UPDATE evaluation SET date_affichage = date');
    }

    public function down(Schema $schema): void
    {
        // cette migration down() est auto-générée, veuillez la modifier selon vos besoins
        $this->addSql('ALTER TABLE evaluation CHANGE date_affichage date_affiche DATETIME DEFAULT NULL');
    }
}
