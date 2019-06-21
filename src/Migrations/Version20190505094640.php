<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190505094640 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE anime (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(255) NOT NULL, descripcion VARCHAR(255) NOT NULL, etiquetas VARCHAR(255) NOT NULL, valoracion SMALLINT DEFAULT NULL, portada VARCHAR(255) DEFAULT NULL, fecha_publicacion DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        //$this->addSql('ALTER TABLE usuario CHANGE alias alias VARCHAR(15) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE retrato retrato VARCHAR(255) NOT NULL, CHANGE animes_favoritos animes_favoritos VARCHAR(255) NOT NULL, CHANGE intereses intereses VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE anime');
        //$this->addSql('ALTER TABLE usuario CHANGE alias alias VARCHAR(15) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE roles roles TEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE retrato retrato VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE animes_favoritos animes_favoritos VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE intereses intereses VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
