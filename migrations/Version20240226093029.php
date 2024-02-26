<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240226093029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES participant (id)');
        $this->addSql('DROP INDEX UNIQ_D79F6B115126AC48 ON participant');
        $this->addSql('ALTER TABLE participant ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, DROP mail, DROP administrateur');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D79F6B11E7927C74 ON participant (email)');
        $this->addSql('ALTER TABLE sortie ADD is_published TINYINT(1) DEFAULT NULL, DROP etat, CHANGE duree duree INT DEFAULT NULL, CHANGE nb_inscriptions_max nb_inscriptions_max INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('ALTER TABLE sortie ADD etat TINYINT(1) NOT NULL, DROP is_published, CHANGE duree duree TIME NOT NULL, CHANGE nb_inscriptions_max nb_inscriptions_max INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_D79F6B11E7927C74 ON participant');
        $this->addSql('ALTER TABLE participant ADD mail VARCHAR(255) NOT NULL, ADD administrateur TINYINT(1) NOT NULL, DROP email, DROP roles');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D79F6B115126AC48 ON participant (mail)');
    }
}
