<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921235034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role column to provider and client tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE provider ADD role VARCHAR(255) NOT NULL DEFAULT \'provider\'');
        $this->addSql('ALTER TABLE client ADD role VARCHAR(255) NOT NULL DEFAULT \'client\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE provider DROP role');
        $this->addSql('ALTER TABLE client DROP role');
    }
}
