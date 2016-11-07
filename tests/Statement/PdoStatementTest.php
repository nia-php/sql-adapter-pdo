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
namespace Test\Nia\Sql\Adapter\Statement;

use PHPUnit_Framework_TestCase;
use Nia\Sql\Adapter\Statement\PdoStatement;
use PDO;
use Nia\Sql\Adapter\Statement\StatementInterface;

/**
 * Unit test for \Nia\Sql\Adapter\Statement\PdoStatement.
 */
class PdoStatementTest extends PHPUnit_Framework_TestCase
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
     * @covers \Nia\Sql\Adapter\Statement\PdoStatement
     */
    public function testMethods()
    {
        $sqlStatement = "SELECT id FROM test WHERE id = :id1 OR id = :id2;";
        $pdoStatement = $this->pdo->prepare($sqlStatement);

        $statement = new PdoStatement($pdoStatement, $sqlStatement);
        $this->assertSame($sqlStatement, $statement->getSqlStatement());

        $statement->bind(':id1', 1, StatementInterface::TYPE_INT);
        $statement->bind(':id2', 3, StatementInterface::TYPE_INT);

        $expectedRows = [
            [
                'id' => '1'
            ],
            [
                'id' => '3'
            ]
        ];

        $statement->execute();
        $this->assertSame(0, $statement->getNumRowsAffected());
        $this->assertSame($expectedRows[0], $statement->fetch());
        $this->assertSame($expectedRows[1], $statement->fetch());

        $statement->execute();
        $this->assertSame($expectedRows, $statement->fetchAll());

        $statement->execute();
        $this->assertSame($expectedRows, iterator_to_array($statement));

        $this->setExpectedException(\OutOfBoundsException::class, 'no row found.');
        $statement->fetch();
    }
}
