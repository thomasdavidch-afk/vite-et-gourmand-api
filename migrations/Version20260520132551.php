<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520132551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE adapte DROP FOREIGN KEY `adapte_ibfk_1`');
        $this->addSql('ALTER TABLE adapte DROP FOREIGN KEY `adapte_ibfk_2`');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `commande_ibfk_1`');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `commande_ibfk_2`');
        $this->addSql('ALTER TABLE contient DROP FOREIGN KEY `contient_ibfk_1`');
        $this->addSql('ALTER TABLE contient DROP FOREIGN KEY `contient_ibfk_2`');
        $this->addSql('ALTER TABLE menu_plat DROP FOREIGN KEY `menu_plat_ibfk_1`');
        $this->addSql('ALTER TABLE menu_plat DROP FOREIGN KEY `menu_plat_ibfk_2`');
        $this->addSql('ALTER TABLE menu_theme DROP FOREIGN KEY `menu_theme_ibfk_1`');
        $this->addSql('ALTER TABLE menu_theme DROP FOREIGN KEY `menu_theme_ibfk_2`');
        $this->addSql('ALTER TABLE possede DROP FOREIGN KEY `possede_ibfk_1`');
        $this->addSql('ALTER TABLE possede DROP FOREIGN KEY `possede_ibfk_2`');
        $this->addSql('ALTER TABLE publie DROP FOREIGN KEY `publie_ibfk_1`');
        $this->addSql('ALTER TABLE publie DROP FOREIGN KEY `publie_ibfk_2`');
        $this->addSql('DROP TABLE adapte');
        $this->addSql('DROP TABLE allergene');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE contient');
        $this->addSql('DROP TABLE horaire');
        $this->addSql('DROP TABLE menu_plat');
        $this->addSql('DROP TABLE menu_theme');
        $this->addSql('DROP TABLE plat');
        $this->addSql('DROP TABLE possede');
        $this->addSql('DROP TABLE publie');
        $this->addSql('DROP TABLE regime');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('ALTER TABLE menu MODIFY menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE menu CHANGE titre titre VARCHAR(50) DEFAULT NULL, CHANGE prix_par_personne prix_par_personne DOUBLE PRECISION DEFAULT NULL, CHANGE regime regime VARCHAR(50) DEFAULT NULL, CHANGE description description VARCHAR(50) DEFAULT NULL, CHANGE menu_id id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adapte (menu_id INT NOT NULL, regime_id INT NOT NULL, INDEX regime_id (regime_id), INDEX IDX_BF387DC2CCD7E912 (menu_id), PRIMARY KEY (menu_id, regime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE allergene (allergene_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (allergene_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE avis (avis_id INT AUTO_INCREMENT NOT NULL, note VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, description VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (avis_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE commande (numero_commande VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_commande DATE NOT NULL, date_prestation DATE NOT NULL, heure_livraison VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prix_menu DOUBLE PRECISION NOT NULL, nombre_personne INT NOT NULL, prix_livraison DOUBLE PRECISION NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, pret_materiel TINYINT NOT NULL, restitution_materiel TINYINT NOT NULL, menu_id INT DEFAULT NULL, utilisateur_id INT DEFAULT NULL, INDEX menu_id (menu_id), INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (numero_commande)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE contient (plat_id INT NOT NULL, allergene_id INT NOT NULL, INDEX allergene_id (allergene_id), INDEX IDX_DC302E56D73DB560 (plat_id), PRIMARY KEY (plat_id, allergene_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE horaire (horaire_id INT AUTO_INCREMENT NOT NULL, jour VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, heure_ouverture VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, heure_fermeture VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (horaire_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE menu_plat (menu_id INT NOT NULL, plat_id INT NOT NULL, INDEX plat_id (plat_id), INDEX IDX_E8775249CCD7E912 (menu_id), PRIMARY KEY (menu_id, plat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE menu_theme (menu_id INT NOT NULL, theme_id INT NOT NULL, INDEX theme_id (theme_id), INDEX IDX_6D9C46FCCD7E912 (menu_id), PRIMARY KEY (menu_id, theme_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE plat (plat_id INT AUTO_INCREMENT NOT NULL, titre_plat VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, photo BLOB DEFAULT NULL, PRIMARY KEY (plat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE possede (utilisateur_id INT NOT NULL, role_id INT NOT NULL, INDEX role_id (role_id), INDEX IDX_3D0B1508FB88E14F (utilisateur_id), PRIMARY KEY (utilisateur_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE publie (utilisateur_id INT NOT NULL, avis_id INT NOT NULL, INDEX avis_id (avis_id), INDEX IDX_D2D78B28FB88E14F (utilisateur_id), PRIMARY KEY (utilisateur_id, avis_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE regime (regime_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (regime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role (role_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE theme (theme_id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (theme_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE utilisateur (utilisateur_id INT AUTO_INCREMENT NOT NULL, email VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, password VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, prenom VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, telephone VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, ville VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, pays VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, adresse_postale VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE adapte ADD CONSTRAINT `adapte_ibfk_1` FOREIGN KEY (menu_id) REFERENCES menu (menu_id)');
        $this->addSql('ALTER TABLE adapte ADD CONSTRAINT `adapte_ibfk_2` FOREIGN KEY (regime_id) REFERENCES regime (regime_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (menu_id) REFERENCES menu (menu_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE contient ADD CONSTRAINT `contient_ibfk_1` FOREIGN KEY (plat_id) REFERENCES plat (plat_id)');
        $this->addSql('ALTER TABLE contient ADD CONSTRAINT `contient_ibfk_2` FOREIGN KEY (allergene_id) REFERENCES allergene (allergene_id)');
        $this->addSql('ALTER TABLE menu_plat ADD CONSTRAINT `menu_plat_ibfk_1` FOREIGN KEY (menu_id) REFERENCES menu (menu_id)');
        $this->addSql('ALTER TABLE menu_plat ADD CONSTRAINT `menu_plat_ibfk_2` FOREIGN KEY (plat_id) REFERENCES plat (plat_id)');
        $this->addSql('ALTER TABLE menu_theme ADD CONSTRAINT `menu_theme_ibfk_1` FOREIGN KEY (menu_id) REFERENCES menu (menu_id)');
        $this->addSql('ALTER TABLE menu_theme ADD CONSTRAINT `menu_theme_ibfk_2` FOREIGN KEY (theme_id) REFERENCES theme (theme_id)');
        $this->addSql('ALTER TABLE possede ADD CONSTRAINT `possede_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE possede ADD CONSTRAINT `possede_ibfk_2` FOREIGN KEY (role_id) REFERENCES role (role_id)');
        $this->addSql('ALTER TABLE publie ADD CONSTRAINT `publie_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE publie ADD CONSTRAINT `publie_ibfk_2` FOREIGN KEY (avis_id) REFERENCES avis (avis_id)');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE menu MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE menu CHANGE titre titre VARCHAR(50) DEFAULT \'NULL\', CHANGE prix_par_personne prix_par_personne DOUBLE PRECISION DEFAULT \'NULL\', CHANGE regime regime VARCHAR(50) DEFAULT \'NULL\', CHANGE description description VARCHAR(50) DEFAULT \'NULL\', CHANGE id menu_id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (menu_id)');
    }
}
