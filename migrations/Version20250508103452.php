<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508103452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE `refresh_token` (
                id INT AUTO_INCREMENT NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                valid_at DATETIME NOT NULL, 
                user_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_C74F21955F37A13B (token), 
                INDEX IDX_C74F2195A76ED395 (user_id), 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
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
                UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `refresh_token` ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `refresh_token` DROP FOREIGN KEY FK_C74F2195A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `refresh_token`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
    }
}
