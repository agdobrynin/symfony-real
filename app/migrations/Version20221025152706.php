<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221025152706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification (id BIGINT NOT NULL, user_uuid UUID DEFAULT NULL, post_uuid UUID DEFAULT NULL, seen BOOLEAN NOT NULL, dicsr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CAABFE1C6F ON notification (user_uuid)');
        $this->addSql('CREATE INDEX IDX_BF5476CA182A37AD ON notification (post_uuid)');
        $this->addSql('COMMENT ON COLUMN notification.user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN notification.post_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA182A37AD FOREIGN KEY (post_uuid) REFERENCES micro_post (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('DROP TABLE notification');
    }
}
