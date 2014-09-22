<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140922173440 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE dan_vo_desire (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, game_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, note LONGTEXT DEFAULT NULL, reward INT DEFAULT NULL, INDEX IDX_BB71E3CF7E3C61F9 (owner_id), INDEX IDX_BB71E3CFE48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE dan_vo_desire_user (id INT AUTO_INCREMENT NOT NULL, desire_id INT DEFAULT NULL, user_id INT DEFAULT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_2D3B429A9B1C5641 (desire_id), INDEX IDX_2D3B429AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE dan_vo_desire ADD CONSTRAINT FK_BB71E3CF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES dan_user (id)");
        $this->addSql("ALTER TABLE dan_vo_desire ADD CONSTRAINT FK_BB71E3CFE48FD905 FOREIGN KEY (game_id) REFERENCES dan_vo_game (id)");
        $this->addSql("ALTER TABLE dan_vo_desire_user ADD CONSTRAINT FK_2D3B429A9B1C5641 FOREIGN KEY (desire_id) REFERENCES dan_vo_desire (id)");
        $this->addSql("ALTER TABLE dan_vo_desire_user ADD CONSTRAINT FK_2D3B429AA76ED395 FOREIGN KEY (user_id) REFERENCES dan_user (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE dan_vo_desire_user DROP FOREIGN KEY FK_2D3B429A9B1C5641");
        $this->addSql("DROP TABLE dan_vo_desire");
        $this->addSql("DROP TABLE dan_vo_desire_user");
    }
}
