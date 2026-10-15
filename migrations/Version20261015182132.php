<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015182132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE author (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(180) NOT NULL, online BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDAFD8C8F85E0677 ON author (username)');
        $this->addSql('CREATE TABLE post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(120) NOT NULL, excerpt VARCHAR(280) NOT NULL, category VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, tags CLOB NOT NULL, views INTEGER NOT NULL, comments INTEGER NOT NULL, reading_time INTEGER NOT NULL, featured BOOLEAN NOT NULL, published_at DATETIME DEFAULT NULL, author_id INTEGER NOT NULL, CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES author (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D2B36786B ON post (title)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D7B00651C ON post (status)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D64C19C1 ON post (category)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DE0D4FDE1 ON post (published_at)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE post');
    }
}
