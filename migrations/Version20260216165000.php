<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create security persistence tables and seed initial admin account.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE security_users (id VARCHAR(64) NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, roles JSON NOT NULL, failed_attempts INT NOT NULL, locked_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_security_users_email ON security_users (email)');

        $this->addSql('CREATE TABLE second_factor_challenges (id VARCHAR(64) NOT NULL, user_id VARCHAR(64) NOT NULL, code_hash VARCHAR(255) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, max_attempts INT NOT NULL, attempts INT NOT NULL, verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_second_factor_user_id ON second_factor_challenges (user_id)');

        $this->addSql('CREATE TABLE security_audit_events (id VARCHAR(64) NOT NULL, event_name VARCHAR(120) NOT NULL, metadata JSON NOT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_security_audit_events_event_name ON security_audit_events (event_name)');
        $this->addSql('CREATE INDEX idx_security_audit_events_occurred_at ON security_audit_events (occurred_at)');

        $this->addSql("INSERT INTO security_users (id, email, password_hash, roles, failed_attempts, locked_until)
            VALUES (
                'user_admin_1',
                'admin@example.com',
                '\$2y\$12\$1DhR3C6trehv1/qyG9DNPO.ffD8I/YMalDxrQZDVaJjFoIG3NT0Z6',
                '[\"ROLE_ADMIN\"]',
                0,
                NULL
            )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE security_audit_events');
        $this->addSql('DROP TABLE second_factor_challenges');
        $this->addSql('DROP TABLE security_users');
    }
}
