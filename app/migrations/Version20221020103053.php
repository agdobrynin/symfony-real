<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020103053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE micro_post ADD user_uuid UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN micro_post.user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE micro_post ADD CONSTRAINT FK_2AEFE017ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2AEFE017ABFE1C6F ON micro_post (user_uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE micro_post DROP CONSTRAINT FK_2AEFE017ABFE1C6F');
        $this->addSql('DROP INDEX IDX_2AEFE017ABFE1C6F');
        $this->addSql('ALTER TABLE micro_post DROP user_uuid');
    }
}
