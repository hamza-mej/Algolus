<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211231141435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_details');
        $this->addSql('ALTER TABLE details ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE details ADD CONSTRAINT FK_72260B8A4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_72260B8A4584665A ON details (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_details (product_id INT NOT NULL, details_id INT NOT NULL, INDEX IDX_A3FF103A4584665A (product_id), INDEX IDX_A3FF103ABB1A0722 (details_id), PRIMARY KEY(product_id, details_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_details ADD CONSTRAINT FK_A3FF103A4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_details ADD CONSTRAINT FK_A3FF103ABB1A0722 FOREIGN KEY (details_id) REFERENCES details (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE details DROP FOREIGN KEY FK_72260B8A4584665A');
        $this->addSql('DROP INDEX IDX_72260B8A4584665A ON details');
        $this->addSql('ALTER TABLE details DROP product_id');
    }
}
