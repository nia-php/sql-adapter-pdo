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
namespace Nia\Sql\Adapter\Statement;

use Iterator;
use OutOfBoundsException;
use PDO;
use PdoStatement as NativePdoStatement;
use RuntimeException;

/**
 * Prepared statement implementation using PDO.
 */
class PdoStatement implements StatementInterface
{

    /**
     * The decorated pdo statement.
     *
     * @var NativePdoStatement
     */
    private $pdoStatement = null;

    /**
     * The prepared sql statement.
     *
     * @var string
     */
    private $sqlStatement = null;

    /**
     * Constructor.
     *
     * @param NativePdoStatement $pdoStatement
     *            The decorated pdo statement.
     * @param string $sqlStatement
     *            The prepared sql statement.
     */
    public function __construct(NativePdoStatement $pdoStatement, string $sqlStatement)
    {
        $this->pdoStatement = $pdoStatement;
        $this->sqlStatement = $sqlStatement;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\Statement\StatementInterface::getSqlStatement()
     */
    public function getSqlStatement(): string
    {
        return $this->sqlStatement;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\Statement\StatementInterface::bind()
     */
    public function bind(string $name, $value, int $type = null)
    {
        $mapping = [
            self::TYPE_STRING => PDO::PARAM_STR,
            self::TYPE_INT => PDO::PARAM_INT,
            self::TYPE_DECIMAL => PDO::PARAM_STR,
            self::TYPE_BOOL => PDO::PARAM_BOOL,
            self::TYPE_BINARY => PDO::PARAM_LOB,
            self::TYPE_NULL => PDO::PARAM_NULL
        ];

        if (! array_key_exists($type, $mapping)) {
            $type = self::TYPE_STRING;
        }

        if ($type === self::TYPE_DECIMAL) {
            $value = (float) $value;
        }

        $this->pdoStatement->bindValue($name, $value, $mapping[$type]);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\Statement\StatementInterface::execute()
     */
    public function execute()
    {
        if (! $this->pdoStatement->execute()) {
            throw new RuntimeException(implode(' - ', $this->pdoStatement->errorInfo()));
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\Statement\StatementInterface::getNumRowsAffected()
     */
    public function getNumRowsAffected(): int
    {
        return (int) $this->pdoStatement->rowCount();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\Statement\StatementInterface::fetch()
     */
    public function fetch(): array
    {
        $result = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

        if (! $result) {
            throw new OutOfBoundsException('no row found.');
        }

        return $result;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\Statement\StatementInterface::fetchAll()
     */
    public function fetchAll(): array
    {
        return $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator(): Iterator
    {
        $generator = function () {
            while (is_array($row = $this->pdoStatement->fetch(PDO::FETCH_ASSOC))) {
                yield $row;
            }
        };

        return $generator();
    }
}
