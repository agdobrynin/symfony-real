<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117144428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526CABFE1C6F');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C182A37AD');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C182A37AD FOREIGN KEY (post_uuid) REFERENCES micro_post (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT fk_9474526cabfe1c6f');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT fk_9474526c182a37ad');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT fk_9474526cabfe1c6f FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT fk_9474526c182a37ad FOREIGN KEY (post_uuid) REFERENCES micro_post (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
