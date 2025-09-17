<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914173948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, language_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, summary VARCHAR(255) NOT NULL, is_published TINYINT(1) NOT NULL, is_featured TINYINT(1) NOT NULL, cover VARCHAR(255) NOT NULL, published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_23A0E66A53A8AA (provider_id), INDEX IDX_23A0E6682F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_content (id INT AUTO_INCREMENT NOT NULL, article_section_id INT NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_1317741EE4FA32FB (article_section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_image (id INT AUTO_INCREMENT NOT NULL, article_content_id INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_B28A764EB879726C (article_content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_section (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_C0A13E587294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE availability_slot (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, start_time VARCHAR(255) NOT NULL, end_time VARCHAR(255) NOT NULL, is_booked TINYINT(1) NOT NULL, INDEX IDX_1C11DC9EA53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, slot_id INT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E00CEDDE19EB6921 (client_id), INDEX IDX_E00CEDDE59E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, profile_picture VARCHAR(255) NOT NULL, joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', slug VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_C7440455F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE completed_work (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, company VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, start_date VARCHAR(255) NOT NULL, end_date VARCHAR(255) NOT NULL, INDEX IDX_37CC9DBCA53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE completed_work_media (id INT AUTO_INCREMENT NOT NULL, work_id INT NOT NULL, media_type VARCHAR(255) NOT NULL, media_url VARCHAR(255) NOT NULL, INDEX IDX_B935DF94BB3453DB (work_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE education (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, title VARCHAR(255) NOT NULL, institution_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', institution_image VARCHAR(255) NOT NULL, INDEX IDX_DB0A5ED2A53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, title VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, first_task VARCHAR(255) NOT NULL, second_task VARCHAR(255) NOT NULL, third_task VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', company_logo VARCHAR(255) NOT NULL, INDEX IDX_590C103A53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hard_skill_provider (hard_skill_id INT NOT NULL, provider_id INT NOT NULL, INDEX IDX_3682EFD5B7DB062 (hard_skill_id), INDEX IDX_3682EFD5A53A8AA (provider_id), PRIMARY KEY(hard_skill_id, provider_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE language_provider (language_id INT NOT NULL, provider_id INT NOT NULL, INDEX IDX_FE662BA682F1BAF4 (language_id), INDEX IDX_FE662BA6A53A8AA (provider_id), PRIMARY KEY(language_id, provider_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, recipient_id INT NOT NULL, recipient_type VARCHAR(255) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, provider_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3B978F9F19EB6921 (client_id), INDEX IDX_3B978F9FA53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, provider_id INT NOT NULL, comment VARCHAR(255) NOT NULL, rating VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_794381C619EB6921 (client_id), INDEX IDX_794381C6A53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, title VARCHAR(255) NOT NULL, summary VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, min_price VARCHAR(255) NOT NULL, max_price VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, is_featured TINYINT(1) NOT NULL, cover VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E19D9AD2A53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_content (id INT AUTO_INCREMENT NOT NULL, service_section_id INT NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_314162354E72DACE (service_section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_image (id INT AUTO_INCREMENT NOT NULL, service_content_id INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_6C4FE9B812F19A59 (service_content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_section (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_E2F72873ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_link (id INT AUTO_INCREMENT NOT NULL, provider_id INT NOT NULL, platform VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_79BD4A95A53A8AA (provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE soft_skill_provider (soft_skill_id INT NOT NULL, provider_id INT NOT NULL, INDEX IDX_1464DD5688034CA4 (soft_skill_id), INDEX IDX_1464DD56A53A8AA (provider_id), PRIMARY KEY(soft_skill_id, provider_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_service (tag_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_D3ACB778BAD26311 (tag_id), INDEX IDX_D3ACB778ED5CA9E6 (service_id), PRIMARY KEY(tag_id, service_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_article (tag_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_300B23CCBAD26311 (tag_id), INDEX IDX_300B23CC7294869C (article_id), PRIMARY KEY(tag_id, article_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6682F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE article_content ADD CONSTRAINT FK_1317741EE4FA32FB FOREIGN KEY (article_section_id) REFERENCES article_section (id)');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764EB879726C FOREIGN KEY (article_content_id) REFERENCES article_content (id)');
        $this->addSql('ALTER TABLE article_section ADD CONSTRAINT FK_C0A13E587294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE availability_slot ADD CONSTRAINT FK_1C11DC9EA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE59E5119C FOREIGN KEY (slot_id) REFERENCES availability_slot (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE completed_work ADD CONSTRAINT FK_37CC9DBCA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE completed_work_media ADD CONSTRAINT FK_B935DF94BB3453DB FOREIGN KEY (work_id) REFERENCES completed_work (id)');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE hard_skill_provider ADD CONSTRAINT FK_3682EFD5B7DB062 FOREIGN KEY (hard_skill_id) REFERENCES hard_skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hard_skill_provider ADD CONSTRAINT FK_3682EFD5A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE language_provider ADD CONSTRAINT FK_FE662BA682F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE language_provider ADD CONSTRAINT FK_FE662BA6A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9FA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE service_content ADD CONSTRAINT FK_314162354E72DACE FOREIGN KEY (service_section_id) REFERENCES service_section (id)');
        $this->addSql('ALTER TABLE service_image ADD CONSTRAINT FK_6C4FE9B812F19A59 FOREIGN KEY (service_content_id) REFERENCES service_content (id)');
        $this->addSql('ALTER TABLE service_section ADD CONSTRAINT FK_E2F72873ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE social_link ADD CONSTRAINT FK_79BD4A95A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('ALTER TABLE soft_skill_provider ADD CONSTRAINT FK_1464DD5688034CA4 FOREIGN KEY (soft_skill_id) REFERENCES soft_skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE soft_skill_provider ADD CONSTRAINT FK_1464DD56A53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_service ADD CONSTRAINT FK_D3ACB778BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_service ADD CONSTRAINT FK_D3ACB778ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_article ADD CONSTRAINT FK_300B23CCBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_article ADD CONSTRAINT FK_300B23CC7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE provider ADD job_id INT NOT NULL, ADD country_id INT NOT NULL, ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD password VARCHAR(255) NOT NULL, ADD profile_picture VARCHAR(255) NOT NULL, ADD joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD slug VARCHAR(255) NOT NULL, ADD city VARCHAR(255) NOT NULL, ADD state VARCHAR(255) NOT NULL, ADD postal_code VARCHAR(255) NOT NULL, ADD address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE provider ADD CONSTRAINT FK_92C4739CBE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('ALTER TABLE provider ADD CONSTRAINT FK_92C4739CF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_92C4739CBE04EA9 ON provider (job_id)');
        $this->addSql('CREATE INDEX IDX_92C4739CF92F3E70 ON provider (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66A53A8AA');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6682F1BAF4');
        $this->addSql('ALTER TABLE article_content DROP FOREIGN KEY FK_1317741EE4FA32FB');
        $this->addSql('ALTER TABLE article_image DROP FOREIGN KEY FK_B28A764EB879726C');
        $this->addSql('ALTER TABLE article_section DROP FOREIGN KEY FK_C0A13E587294869C');
        $this->addSql('ALTER TABLE availability_slot DROP FOREIGN KEY FK_1C11DC9EA53A8AA');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE19EB6921');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE59E5119C');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F92F3E70');
        $this->addSql('ALTER TABLE completed_work DROP FOREIGN KEY FK_37CC9DBCA53A8AA');
        $this->addSql('ALTER TABLE completed_work_media DROP FOREIGN KEY FK_B935DF94BB3453DB');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2A53A8AA');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103A53A8AA');
        $this->addSql('ALTER TABLE hard_skill_provider DROP FOREIGN KEY FK_3682EFD5B7DB062');
        $this->addSql('ALTER TABLE hard_skill_provider DROP FOREIGN KEY FK_3682EFD5A53A8AA');
        $this->addSql('ALTER TABLE language_provider DROP FOREIGN KEY FK_FE662BA682F1BAF4');
        $this->addSql('ALTER TABLE language_provider DROP FOREIGN KEY FK_FE662BA6A53A8AA');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F19EB6921');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9FA53A8AA');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C619EB6921');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A53A8AA');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2A53A8AA');
        $this->addSql('ALTER TABLE service_content DROP FOREIGN KEY FK_314162354E72DACE');
        $this->addSql('ALTER TABLE service_image DROP FOREIGN KEY FK_6C4FE9B812F19A59');
        $this->addSql('ALTER TABLE service_section DROP FOREIGN KEY FK_E2F72873ED5CA9E6');
        $this->addSql('ALTER TABLE social_link DROP FOREIGN KEY FK_79BD4A95A53A8AA');
        $this->addSql('ALTER TABLE soft_skill_provider DROP FOREIGN KEY FK_1464DD5688034CA4');
        $this->addSql('ALTER TABLE soft_skill_provider DROP FOREIGN KEY FK_1464DD56A53A8AA');
        $this->addSql('ALTER TABLE tag_service DROP FOREIGN KEY FK_D3ACB778BAD26311');
        $this->addSql('ALTER TABLE tag_service DROP FOREIGN KEY FK_D3ACB778ED5CA9E6');
        $this->addSql('ALTER TABLE tag_article DROP FOREIGN KEY FK_300B23CCBAD26311');
        $this->addSql('ALTER TABLE tag_article DROP FOREIGN KEY FK_300B23CC7294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_content');
        $this->addSql('DROP TABLE article_image');
        $this->addSql('DROP TABLE article_section');
        $this->addSql('DROP TABLE availability_slot');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE completed_work');
        $this->addSql('DROP TABLE completed_work_media');
        $this->addSql('DROP TABLE education');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE hard_skill_provider');
        $this->addSql('DROP TABLE language_provider');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE request');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE service_content');
        $this->addSql('DROP TABLE service_image');
        $this->addSql('DROP TABLE service_section');
        $this->addSql('DROP TABLE social_link');
        $this->addSql('DROP TABLE soft_skill_provider');
        $this->addSql('DROP TABLE tag_service');
        $this->addSql('DROP TABLE tag_article');
        $this->addSql('ALTER TABLE provider DROP FOREIGN KEY FK_92C4739CBE04EA9');
        $this->addSql('ALTER TABLE provider DROP FOREIGN KEY FK_92C4739CF92F3E70');
        $this->addSql('DROP INDEX IDX_92C4739CBE04EA9 ON provider');
        $this->addSql('DROP INDEX IDX_92C4739CF92F3E70 ON provider');
        $this->addSql('ALTER TABLE provider DROP job_id, DROP country_id, DROP first_name, DROP last_name, DROP email, DROP password, DROP profile_picture, DROP joined_at, DROP slug, DROP city, DROP state, DROP postal_code, DROP address');
    }
}
