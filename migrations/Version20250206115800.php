<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206115800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accepted_blog (id INT AUTO_INCREMENT NOT NULL, contenu VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, rate INT NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog ADD statut VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD acc_blog_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC6E69BA85 FOREIGN KEY (acc_blog_id) REFERENCES accepted_blog (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC6E69BA85 ON commentaire (acc_blog_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC6E69BA85');
        $this->addSql('DROP TABLE accepted_blog');
        $this->addSql('ALTER TABLE blog DROP statut');
        $this->addSql('DROP INDEX IDX_67F068BC6E69BA85 ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP acc_blog_id');
    }
}
