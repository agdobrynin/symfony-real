<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221026133220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476ca633c5905');
        $this->addSql('DROP INDEX idx_bf5476ca633c5905');
        $this->addSql('ALTER TABLE notification RENAME COLUMN liked_by_user_uuid TO by_user_uuid');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA3F3B8D8A FOREIGN KEY (by_user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BF5476CA3F3B8D8A ON notification (by_user_uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA3F3B8D8A');
        $this->addSql('DROP INDEX IDX_BF5476CA3F3B8D8A');
        $this->addSql('ALTER TABLE notification RENAME COLUMN by_user_uuid TO liked_by_user_uuid');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476ca633c5905 FOREIGN KEY (liked_by_user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_bf5476ca633c5905 ON notification (liked_by_user_uuid)');
    }
}
