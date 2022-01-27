<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211222103125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE details ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE details ADD CONSTRAINT FK_72260B8A4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_72260B8A4584665A ON details (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE details DROP FOREIGN KEY FK_72260B8A4584665A');
        $this->addSql('DROP INDEX IDX_72260B8A4584665A ON details');
        $this->addSql('ALTER TABLE details DROP product_id');
    }
}
