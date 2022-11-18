<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117093843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (uuid UUID NOT NULL, user_uuid UUID NOT NULL, post_uuid UUID NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content VARCHAR(200) NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_9474526CABFE1C6F ON comment (user_uuid)');
        $this->addSql('CREATE INDEX IDX_9474526C182A37AD ON comment (post_uuid)');
        $this->addSql('COMMENT ON COLUMN comment.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN comment.user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN comment.post_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C182A37AD FOREIGN KEY (post_uuid) REFERENCES micro_post (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE comment');
    }
}
