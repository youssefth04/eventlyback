<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240622172301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organizer DROP FOREIGN KEY FK_99D47173A76ED395');
        $this->addSql('ALTER TABLE organizer CHANGE number_of_tickets number_of_tickets VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE organizer ADD CONSTRAINT FK_99D47173A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organizer DROP FOREIGN KEY FK_99D47173A76ED395');
        $this->addSql('ALTER TABLE organizer CHANGE number_of_tickets number_of_tickets NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE organizer ADD CONSTRAINT FK_99D47173A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }
}
