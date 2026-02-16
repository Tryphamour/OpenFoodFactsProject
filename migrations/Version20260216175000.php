<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216175000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create dashboard widgets table for user layout persistence.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dashboard_widgets (id VARCHAR(64) NOT NULL, owner_id VARCHAR(64) NOT NULL, type VARCHAR(64) NOT NULL, position INT NOT NULL, configuration JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_dashboard_widgets_owner_id ON dashboard_widgets (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_dashboard_widgets_owner_position ON dashboard_widgets (owner_id, position)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dashboard_widgets');
    }
}
