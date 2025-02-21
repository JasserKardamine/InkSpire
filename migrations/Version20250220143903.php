<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220143903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artwork (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, theme VARCHAR(50) NOT NULL, description VARCHAR(100) NOT NULL, picture VARCHAR(100) NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_881FC576A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artwork ADD CONSTRAINT FK_881FC576A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE auction ADD artwork_id INT NOT NULL');
        $this->addSql('ALTER TABLE auction ADD CONSTRAINT FK_DEE4F593DB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artwork (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DEE4F593DB8FFA4 ON auction (artwork_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auction DROP FOREIGN KEY FK_DEE4F593DB8FFA4');
        $this->addSql('ALTER TABLE artwork DROP FOREIGN KEY FK_881FC576A76ED395');
        $this->addSql('DROP TABLE artwork');
        $this->addSql('DROP INDEX UNIQ_DEE4F593DB8FFA4 ON auction');
        $this->addSql('ALTER TABLE auction DROP artwork_id');
    }
}
