<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914225144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5373C9665E237E06 ON country (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9EE9EE462B36786B ON hard_skill (title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBD8E0F8989D9B62 ON job (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4DB71B55E237E06 ON language (name)');
        $this->addSql('ALTER TABLE provider CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE state state VARCHAR(255) DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92C4739CE7927C74 ON provider (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92C4739C989D9B62 ON provider (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_164AECD42B36786B ON soft_skill (title)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE profile_picture profile_picture VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_5373C9665E237E06 ON country');
        $this->addSql('DROP INDEX UNIQ_9EE9EE462B36786B ON hard_skill');
        $this->addSql('DROP INDEX UNIQ_FBD8E0F8989D9B62 ON job');
        $this->addSql('DROP INDEX UNIQ_D4DB71B55E237E06 ON language');
        $this->addSql('DROP INDEX UNIQ_92C4739CE7927C74 ON provider');
        $this->addSql('DROP INDEX UNIQ_92C4739C989D9B62 ON provider');
        $this->addSql('ALTER TABLE provider CHANGE profile_picture profile_picture VARCHAR(255) NOT NULL, CHANGE state state VARCHAR(255) NOT NULL, CHANGE postal_code postal_code VARCHAR(255) NOT NULL, CHANGE address address VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_164AECD42B36786B ON soft_skill');
    }
}
