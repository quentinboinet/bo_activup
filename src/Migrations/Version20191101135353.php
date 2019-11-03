<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191101135353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE device ADD subset_id INT, ADD name VARCHAR(100)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E7687853D FOREIGN KEY (subset_id) REFERENCES subset (id)');
        $this->addSql('CREATE INDEX IDX_92FB68E7687853D ON device (subset_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E7687853D');
        $this->addSql('DROP INDEX IDX_92FB68E7687853D ON device');
        $this->addSql('ALTER TABLE device DROP subset_id, DROP name');
    }
}
