<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114195142 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE file_hub');
        $this->addSql('DROP INDEX IDX_CABCB492A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__hub AS SELECT id, user_id, token, name, path, create_date, update_date, slug, description FROM hub');
        $this->addSql('DROP TABLE hub');
        $this->addSql('CREATE TABLE hub (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(255) NOT NULL COLLATE BINARY, name VARCHAR(255) NOT NULL COLLATE BINARY, path VARCHAR(1000) DEFAULT NULL COLLATE BINARY, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, slug VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_CABCB492A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO hub (id, user_id, token, name, path, create_date, update_date, slug, description) SELECT id, user_id, token, name, path, create_date, update_date, slug, description FROM __temp__hub');
        $this->addSql('DROP TABLE __temp__hub');
        $this->addSql('CREATE INDEX IDX_CABCB492A76ED395 ON hub (user_id)');
        $this->addSql('DROP INDEX IDX_9474526CA76ED395');
        $this->addSql('DROP INDEX IDX_9474526C93CB796C');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, file_id, user_id, content, create_date, update_date FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, content CLOB NOT NULL COLLATE BINARY, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, CONSTRAINT FK_9474526C93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO comment (id, file_id, user_id, content, create_date, update_date) SELECT id, file_id, user_id, content, create_date, update_date FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('CREATE INDEX IDX_9474526C93CB796C ON comment (file_id)');
        $this->addSql('DROP INDEX IDX_AC6340B3F8697D13');
        $this->addSql('DROP INDEX IDX_AC6340B393CB796C');
        $this->addSql('DROP INDEX IDX_AC6340B3DAA83D7F');
        $this->addSql('DROP INDEX IDX_AC6340B3A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__like AS SELECT id, user_id, hub_id, file_id, comment_id, create_date FROM "like"');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, hub_id INTEGER DEFAULT NULL, file_id INTEGER DEFAULT NULL, comment_id INTEGER DEFAULT NULL, create_date DATETIME NOT NULL, CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B3DAA83D7F FOREIGN KEY (hub_id) REFERENCES hub (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B393CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B3F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "like" (id, user_id, hub_id, file_id, comment_id, create_date) SELECT id, user_id, hub_id, file_id, comment_id, create_date FROM __temp__like');
        $this->addSql('DROP TABLE __temp__like');
        $this->addSql('CREATE INDEX IDX_AC6340B3F8697D13 ON "like" (comment_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B393CB796C ON "like" (file_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3DAA83D7F ON "like" (hub_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('DROP INDEX IDX_8C9F3610A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__file AS SELECT id, user_id, token, filename, metadata, create_date, update_date, checksum, description FROM file');
        $this->addSql('DROP TABLE file');
        $this->addSql('CREATE TABLE file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL COLLATE BINARY, filename VARCHAR(1000) NOT NULL COLLATE BINARY, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, checksum VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, metadata CLOB DEFAULT NULL --(DC2Type:array)
        , CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO file (id, user_id, token, filename, metadata, create_date, update_date, checksum, description) SELECT id, user_id, token, filename, metadata, create_date, update_date, checksum, description FROM __temp__file');
        $this->addSql('DROP TABLE __temp__file');
        $this->addSql('CREATE INDEX IDX_8C9F3610A76ED395 ON file (user_id)');
        $this->addSql('DROP INDEX IDX_629089A693CB796C');
        $this->addSql('DROP INDEX IDX_629089A6BAD26311');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tag_file AS SELECT tag_id, file_id FROM tag_file');
        $this->addSql('DROP TABLE tag_file');
        $this->addSql('CREATE TABLE tag_file (tag_id INTEGER NOT NULL, file_id INTEGER NOT NULL, PRIMARY KEY(tag_id, file_id), CONSTRAINT FK_629089A6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_629089A693CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO tag_file (tag_id, file_id) SELECT tag_id, file_id FROM __temp__tag_file');
        $this->addSql('DROP TABLE __temp__tag_file');
        $this->addSql('CREATE INDEX IDX_629089A693CB796C ON tag_file (file_id)');
        $this->addSql('CREATE INDEX IDX_629089A6BAD26311 ON tag_file (tag_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE file_hub (file_id INTEGER NOT NULL, hub_id INTEGER NOT NULL, PRIMARY KEY(file_id, hub_id))');
        $this->addSql('CREATE INDEX IDX_7BEC3B0FDAA83D7F ON file_hub (hub_id)');
        $this->addSql('CREATE INDEX IDX_7BEC3B0F93CB796C ON file_hub (file_id)');
        $this->addSql('DROP INDEX IDX_9474526C93CB796C');
        $this->addSql('DROP INDEX IDX_9474526CA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, file_id, user_id, content, create_date, update_date FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, content CLOB NOT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL)');
        $this->addSql('INSERT INTO comment (id, file_id, user_id, content, create_date, update_date) SELECT id, file_id, user_id, content, create_date, update_date FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526C93CB796C ON comment (file_id)');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('DROP INDEX IDX_8C9F3610A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__file AS SELECT id, user_id, token, filename, metadata, create_date, update_date, checksum, description FROM file');
        $this->addSql('DROP TABLE file');
        $this->addSql('CREATE TABLE file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, filename VARCHAR(1000) NOT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, checksum VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, metadata CLOB DEFAULT \'NULL --(DC2Type:array)\' COLLATE BINARY --(DC2Type:array)
        )');
        $this->addSql('INSERT INTO file (id, user_id, token, filename, metadata, create_date, update_date, checksum, description) SELECT id, user_id, token, filename, metadata, create_date, update_date, checksum, description FROM __temp__file');
        $this->addSql('DROP TABLE __temp__file');
        $this->addSql('CREATE INDEX IDX_8C9F3610A76ED395 ON file (user_id)');
        $this->addSql('DROP INDEX IDX_AC6340B3A76ED395');
        $this->addSql('DROP INDEX IDX_AC6340B3DAA83D7F');
        $this->addSql('DROP INDEX IDX_AC6340B393CB796C');
        $this->addSql('DROP INDEX IDX_AC6340B3F8697D13');
        $this->addSql('CREATE TEMPORARY TABLE __temp__like AS SELECT id, user_id, hub_id, file_id, comment_id, create_date FROM "like"');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, hub_id INTEGER DEFAULT NULL, file_id INTEGER DEFAULT NULL, comment_id INTEGER DEFAULT NULL, create_date DATETIME NOT NULL)');
        $this->addSql('INSERT INTO "like" (id, user_id, hub_id, file_id, comment_id, create_date) SELECT id, user_id, hub_id, file_id, comment_id, create_date FROM __temp__like');
        $this->addSql('DROP TABLE __temp__like');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3DAA83D7F ON "like" (hub_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B393CB796C ON "like" (file_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3F8697D13 ON "like" (comment_id)');
        $this->addSql('DROP INDEX IDX_CABCB492A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__hub AS SELECT id, user_id, token, name, path, create_date, update_date, slug, description FROM hub');
        $this->addSql('DROP TABLE hub');
        $this->addSql('CREATE TABLE hub (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(1000) DEFAULT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO hub (id, user_id, token, name, path, create_date, update_date, slug, description) SELECT id, user_id, token, name, path, create_date, update_date, slug, description FROM __temp__hub');
        $this->addSql('DROP TABLE __temp__hub');
        $this->addSql('CREATE INDEX IDX_CABCB492A76ED395 ON hub (user_id)');
        $this->addSql('DROP INDEX IDX_629089A6BAD26311');
        $this->addSql('DROP INDEX IDX_629089A693CB796C');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tag_file AS SELECT tag_id, file_id FROM tag_file');
        $this->addSql('DROP TABLE tag_file');
        $this->addSql('CREATE TABLE tag_file (tag_id INTEGER NOT NULL, file_id INTEGER NOT NULL, PRIMARY KEY(tag_id, file_id))');
        $this->addSql('INSERT INTO tag_file (tag_id, file_id) SELECT tag_id, file_id FROM __temp__tag_file');
        $this->addSql('DROP TABLE __temp__tag_file');
        $this->addSql('CREATE INDEX IDX_629089A6BAD26311 ON tag_file (tag_id)');
        $this->addSql('CREATE INDEX IDX_629089A693CB796C ON tag_file (file_id)');
    }
}
