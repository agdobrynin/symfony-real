<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221102173409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_preferences_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_preferences (id BIGINT NOT NULL, locale VARCHAR(2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE "user" ADD preferences_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6497CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES user_preferences (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6497CCD6FB7 ON "user" (preferences_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6497CCD6FB7');
        $this->addSql('DROP SEQUENCE user_preferences_id_seq CASCADE');
        $this->addSql('DROP TABLE user_preferences');
        $this->addSql('DROP INDEX UNIQ_8D93D6497CCD6FB7');
        $this->addSql('ALTER TABLE "user" DROP preferences_id');
    }
}
