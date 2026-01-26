<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260126152508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users and subscriptions tables with indexes and constraints';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscriptions (id CHAR(36) NOT NULL, name VARCHAR(120) NOT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, billing_period VARCHAR(20) NOT NULL, billing_day_of_month SMALLINT DEFAULT NULL, billing_month_of_year SMALLINT DEFAULT NULL, next_billing_date DATE NOT NULL, category VARCHAR(20) DEFAULT NULL, status VARCHAR(20) DEFAULT \'active\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_4778A01A76ED395 (user_id), INDEX idx_subscription_user_status (user_id, status), INDEX idx_subscription_user_billing_date (user_id, next_billing_date), INDEX idx_subscription_user_period (user_id, billing_period), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id CHAR(36) NOT NULL, email VARCHAR(255) NOT NULL, api_key_hash VARCHAR(255) DEFAULT NULL, api_key_prefix VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX idx_user_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        
        // Add check constraint for amount > 0 (MySQL 8.0.16+)
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT chk_subscription_amount_positive CHECK (amount > 0)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A01A76ED395');
        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE users');
    }
}
