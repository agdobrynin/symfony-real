<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221024092721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_likes (post_uuid UUID NOT NULL, user_uuid UUID NOT NULL, PRIMARY KEY(post_uuid, user_uuid))');
        $this->addSql('CREATE INDEX IDX_DED1C292182A37AD ON post_likes (post_uuid)');
        $this->addSql('CREATE INDEX IDX_DED1C292ABFE1C6F ON post_likes (user_uuid)');
        $this->addSql('COMMENT ON COLUMN post_likes.post_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN post_likes.user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C292182A37AD FOREIGN KEY (post_uuid) REFERENCES micro_post (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C292ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_likes');
    }
}
