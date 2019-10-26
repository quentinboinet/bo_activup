<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815183005 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE weekly_stat (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, week INT NOT NULL, distance INT NOT NULL, duration INT NOT NULL, speed DOUBLE PRECISION NOT NULL, INDEX IDX_CBA1431194A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE weekly_stat ADD CONSTRAINT FK_CBA1431194A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_92FB68E19EB6921 ON device (client_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE weekly_stat');
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E19EB6921');
        $this->addSql('DROP INDEX IDX_92FB68E19EB6921 ON device');
    }
}
