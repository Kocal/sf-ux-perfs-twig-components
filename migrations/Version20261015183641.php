<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015183641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, roles CLOB NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__author AS SELECT id, name, username, email, online FROM author');
        $this->addSql('DROP TABLE author');
        $this->addSql('CREATE TABLE author (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(180) NOT NULL, online BOOLEAN NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_BDAFD8C8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO author (id, name, username, email, online) SELECT id, name, username, email, online FROM __temp__author');
        $this->addSql('DROP TABLE __temp__author');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDAFD8C8F85E0677 ON author (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDAFD8C8A76ED395 ON author (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TEMPORARY TABLE __temp__author AS SELECT id, name, username, email, online FROM author');
        $this->addSql('DROP TABLE author');
        $this->addSql('CREATE TABLE author (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(180) NOT NULL, online BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO author (id, name, username, email, online) SELECT id, name, username, email, online FROM __temp__author');
        $this->addSql('DROP TABLE __temp__author');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDAFD8C8F85E0677 ON author (username)');
    }
}
