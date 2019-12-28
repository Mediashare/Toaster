<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114193933 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE stockage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(1000) DEFAULT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_CABCB492A76ED395 ON stockage (user_id)');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, content CLOB NOT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_9474526C93CB796C ON comment (file_id)');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, stockage_id INTEGER DEFAULT NULL, file_id INTEGER DEFAULT NULL, comment_id INTEGER DEFAULT NULL, create_date DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3DAA83D7F ON "like" (stockage_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B393CB796C ON "like" (file_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3F8697D13 ON "like" (comment_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE TABLE file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, filename VARCHAR(1000) NOT NULL, metadata CLOB DEFAULT NULL --(DC2Type:array)
        , create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, checksum VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_8C9F3610A76ED395 ON file (user_id)');
        $this->addSql('CREATE TABLE file_stockage (file_id INTEGER NOT NULL, stockage_id INTEGER NOT NULL, PRIMARY KEY(file_id, stockage_id))');
        $this->addSql('CREATE INDEX IDX_7BEC3B0F93CB796C ON file_stockage (file_id)');
        $this->addSql('CREATE INDEX IDX_7BEC3B0FDAA83D7F ON file_stockage (stockage_id)');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(1000) NOT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, slug VARCHAR(1000) NOT NULL)');
        $this->addSql('CREATE TABLE tag_file (tag_id INTEGER NOT NULL, file_id INTEGER NOT NULL, PRIMARY KEY(tag_id, file_id))');
        $this->addSql('CREATE INDEX IDX_629089A6BAD26311 ON tag_file (tag_id)');
        $this->addSql('CREATE INDEX IDX_629089A693CB796C ON tag_file (file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE stockage');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_stockage');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_file');
    }
}
