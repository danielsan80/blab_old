<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140804145241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE dan_report ADD user_id INT DEFAULT NULL, ADD properties LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)'");
        $this->addSql("ALTER TABLE dan_report ADD CONSTRAINT FK_C38372B2A76ED395 FOREIGN KEY (user_id) REFERENCES dan_user (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_C38372B2A76ED395 ON dan_report (user_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE dan_report DROP FOREIGN KEY FK_C38372B2A76ED395");
        $this->addSql("DROP INDEX IDX_C38372B2A76ED395 ON dan_report");
        $this->addSql("ALTER TABLE dan_report DROP user_id, DROP properties");
    }
}
