<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221022074036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_following (user_uuid UUID NOT NULL, following_user_uuid UUID NOT NULL, PRIMARY KEY(user_uuid, following_user_uuid))');
        $this->addSql('CREATE INDEX IDX_715F0007ABFE1C6F ON user_following (user_uuid)');
        $this->addSql('CREATE INDEX IDX_715F0007E864F38C ON user_following (following_user_uuid)');
        $this->addSql('COMMENT ON COLUMN user_following.user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_following.following_user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_following ADD CONSTRAINT FK_715F0007ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_following ADD CONSTRAINT FK_715F0007E864F38C FOREIGN KEY (following_user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_following');
    }
}
