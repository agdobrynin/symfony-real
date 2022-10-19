<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221019083447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_8d93d649a045a5e9');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN nick_name TO login');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON "user" (login)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_8D93D649AA08CB10');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN login TO nick_name');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649a045a5e9 ON "user" (nick_name)');
    }
}
