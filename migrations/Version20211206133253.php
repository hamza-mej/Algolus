<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211206133253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE size CHANGE size_name size_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD country VARCHAR(255) DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD state VARCHAR(255) DEFAULT NULL, ADD post_code VARCHAR(255) DEFAULT NULL, ADD phone INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE size CHANGE size_name size_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user DROP country, DROP address, DROP city, DROP state, DROP post_code, DROP phone');
    }
}
