<?php

namespace EuF\PortfolioBundle\Migration\Version500;

use Doctrine\DBAL\Connection;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\CoreBundle\Migration\AbstractMigration;
use Doctrine\DBAL\Types\StringType;

class BooleanFieldsMigration extends AbstractMigration
{
    private array $tables = ['tl_portfolio', 'tl_portfolio_archive', 'tl_portfolio_category'];

    private array $columns = ['published', 'featured', 'target', 'fullsize', 'overwriteMeta', 'addImage', 'protected'];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'erdmannfreunde/portfoliobundle Contao 5 update';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist($this->tables)) {
            return false;
        }

        foreach ($this->tables as $table) {
            $columns = $schemaManager->listTableColumns($table);

            foreach ($columns as $currentColumn) {
                $currentColumnName = $currentColumn->getName();

                if (true === in_array($currentColumnName, $this->columns, true)) {
                    if ($currentColumn->getType() instanceof StringType) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        foreach ($this->tables as $table) {
            $columns = $schemaManager->listTableColumns($table);

            foreach ($columns as $currentColumn) {
                $currentColumnName = $currentColumn->getName();

                if (true === in_array($currentColumnName, $this->columns, true)) {
                    if ($currentColumn->getType() instanceof StringType) {
                        $this->connection
                            ->executeQuery(
                                'ALTER TABLE ' . $table . ' CHANGE ' . $currentColumnName . ' ' . $currentColumnName . ' TINYINT(1) NOT NULL DEFAULT 0'
                            );
                    }
                }
            }
        }

        return $this->createResult(
            true,
            'All char fields have been changed to tinyint.'
        );
    }
}