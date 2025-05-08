<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508083831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (
                id INT AUTO_INCREMENT NOT NULL, 
                email VARCHAR(255) NOT NULL, 
                avatar_url VARCHAR(512) NOT NULL, 
                first_name VARCHAR(100) NOT NULL, 
                last_name VARCHAR(100) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                roles JSON NOT NULL, 
                created_at DATETIME NOT NULL, 
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
    }
}
