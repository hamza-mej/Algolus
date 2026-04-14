<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260414104500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `notification` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, action_url VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, email_sent TINYINT(1) DEFAULT 1 NOT NULL, severity VARCHAR(50) DEFAULT \'info\' NOT NULL, product_id INT DEFAULT NULL, INDEX IDX_6005EF5A76ED395 (user_id), INDEX IDX_6005EF5A8F7B5F76 (product_id), INDEX IDX_6005EF5AA76ED395 (user_id, created_at), INDEX IDX_6005EF5AE89D1C4F (user_id, is_read), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `notification` ADD CONSTRAINT FK_6005EF5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `notification` ADD CONSTRAINT FK_6005EF5A8F7B5F76 FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `notification` DROP FOREIGN KEY FK_6005EF5A76ED395');
        $this->addSql('ALTER TABLE `notification` DROP FOREIGN KEY FK_6005EF5A8F7B5F76');
        $this->addSql('DROP TABLE `notification`');
    }
}
