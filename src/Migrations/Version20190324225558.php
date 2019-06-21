<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190324225558 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE usuario_no_registrado (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(180) NOT NULL, apellidos VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, alias VARCHAR(15) NOT NULL, fecha_alta DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuario CHANGE alias alias VARCHAR(15) NOT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE usuario_no_registrado');
        $this->addSql('ALTER TABLE usuario CHANGE alias alias VARCHAR(15) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE roles roles TEXT NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
