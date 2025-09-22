<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922004533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD birth_date DATE DEFAULT NULL, DROP role');
        $this->addSql('ALTER TABLE provider ADD birth_date DATE DEFAULT NULL, DROP role');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD role VARCHAR(255) DEFAULT \'client\' NOT NULL, DROP birth_date');
        $this->addSql('ALTER TABLE provider ADD role VARCHAR(255) DEFAULT \'provider\' NOT NULL, DROP birth_date');
    }
}
