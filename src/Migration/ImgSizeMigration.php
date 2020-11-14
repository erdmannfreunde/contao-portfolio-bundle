<?php

namespace EuF\PortfolioBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ImgSizeMigration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager !== null && !$schemaManager->tablesExist(['tl_portfolio'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_portfolio');

        return isset($columns['imgSize']) && !isset($columns['size']);
    }

    /**
     * @return MigrationResult
     * @throws Exception
     */
    public function run(): MigrationResult
    {
        $this->connection->executeQuery("ALTER TABLE tl_portfolio CHANGE imgSize size VARCHAR(64) NOT NULL default ''");

        return new MigrationResult(
            true,
            'Renamed tl_portfolio.imgSize to tl_portfolio.size.'
        );
    }
}
