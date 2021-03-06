<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Factory;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class DblibDatabase extends AbstractDatabase
{

    public static function prepareEnvironment(UriInterface $uri)
    {
        $database = preg_replace('~^/~', '', $uri->getPath());

        $customUri = new Uri($uri->__toString());

        $dbDriver = Factory::getDbRelationalInstance($customUri->withPath('/')->__toString());
        $dbDriver->execute("IF NOT EXISTS(select * from sys.databases where name='$database') CREATE DATABASE $database");
    }

    public function createDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("IF NOT EXISTS(select * from sys.databases where name='$database') CREATE DATABASE $database");
        $this->getDbDriver()->execute("USE $database");
    }

    public function dropDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("use master");
        $this->getDbDriver()->execute("drop database $database");
    }

    protected function createTableIfNotExists($database, $createTable)
    {
        $this->getDbDriver()->execute("use $database");

        $sql = "IF (NOT EXISTS (SELECT * 
                 FROM INFORMATION_SCHEMA.TABLES 
                 WHERE TABLE_SCHEMA = 'dbo' 
                 AND  TABLE_NAME = '" . $this->getMigrationTable() . "'))
            BEGIN
                $createTable
            END";

        $this->getDbDriver()->execute($sql);
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    public function createVersion()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());
        $createTable = 'CREATE TABLE ' . $this->getMigrationTable() . ' (version int, status varchar(20))';
        $this->createTableIfNotExists($database, $createTable);
        $this->checkExistsVersion();
    }

    public function executeSql($sql)
    {
        $statements = explode(";", $sql);

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal($sql)
    {
        $this->getDbDriver()->execute($sql);
    }
}
