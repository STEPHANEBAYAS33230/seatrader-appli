<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210429093120 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE produit_famille_produit');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE produit_famille_produit (produit_id INT NOT NULL, famille_produit_id INT NOT NULL, INDEX IDX_C5C126DBF347EFB (produit_id), INDEX IDX_C5C126DBFBC0E351 (famille_produit_id), PRIMARY KEY(produit_id, famille_produit_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE produit_famille_produit ADD CONSTRAINT FK_C5C126DBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_famille_produit ADD CONSTRAINT FK_C5C126DBFBC0E351 FOREIGN KEY (famille_produit_id) REFERENCES famille_produit (id) ON DELETE CASCADE');
    }
}
