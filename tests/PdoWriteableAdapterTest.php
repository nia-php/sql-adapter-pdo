<?php
/*
 * This file is part of the nia framework.
 *
 * (c) Patrick Ullmann <patrick.ullmann@nat-software.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);
namespace Test\Nia\Sql\Adapter;

use PHPUnit_Framework_TestCase;
use Nia\Sql\Adapter\PdoWriteableAdapter;
use PDO;
use Nia\Sql\Adapter\Statement\PdoStatement;

/**
 * Unit test for \Nia\Sql\Adapter\PdoWriteableAdapter.
 */
class PdoWriteableAdapterTest extends PHPUnit_Framework_TestCase
{

    /** @var PDO */
    private $pdo = null;

    /** @var string */
    private $databaseFile = null;

    /**
     *
     * {@inheritdoc}
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->databaseFile = tempnam('/tmp', 'unittest-') . '.sqlite3';

        $sql = <<<SQL
DROP TABLE IF EXISTS `test`;
CREATE TABLE IF NOT EXISTS `test`
(
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `string` TEXT NOT NULL DEFAULT '',
    `int` INTEGER NOT NULL DEFAULT '0',
    `decimal` REAL NOT NULL DEFAULT '0',
    `bool` INTEGER NOT NULL DEFAULT '0',
    `nulled` INTEGER NULL
);
INSERT INTO test(id) VALUES(NULL);
INSERT INTO test(id) VALUES(NULL);
INSERT INTO test(id) VALUES(NULL);
SQL;
        $this->pdo = new PDO('sqlite:' . $this->databaseFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $this->pdo->exec($sql);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->pdo = null;

        if (is_file($this->databaseFile)) {
            unlink($this->databaseFile);
        }
    }

    /**
     * @covers \Nia\Sql\Adapter\PdoWriteableAdapter
     */
    public function testMethods()
    {
        $adapter = new PdoWriteableAdapter($this->pdo);

        $this->assertSame($this->pdo, $adapter->getPdo());

        // namend placeholders.
        $sqlStatement = "INSERT INTO test(string) VALUES(:string);";
        $statement = $adapter->prepare($sqlStatement);

        $this->assertInstanceOf(PdoStatement::class, $statement);

        $statement->bind(':string', 'foobar');
        $statement->execute();

        $this->assertSame(4, $adapter->getLastInsertId());

        // question mark placeholders.
        $sqlStatement = "INSERT INTO test(string) VALUES(?);";
        $statement = $adapter->prepare($sqlStatement);

        $statement->bindIndex(1, 'foobaz');
        $statement->execute();

        $this->assertSame(5, $adapter->getLastInsertId());

        // transactions.
        $this->assertSame(false, $adapter->inTransaction());
        $adapter->startTransaction();
        $this->assertSame(true, $adapter->inTransaction());
        $adapter->commitTransaction();
        $this->assertSame(false, $adapter->inTransaction());

        $adapter->startTransaction();
        $this->assertSame(true, $adapter->inTransaction());
        $adapter->rollBackTransaction();
        $this->assertSame(false, $adapter->inTransaction());
    }
}

