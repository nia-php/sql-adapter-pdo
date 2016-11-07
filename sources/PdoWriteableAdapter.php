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
namespace Nia\Sql\Adapter;

use Nia\Sql\Adapter\Statement\PdoStatement;
use Nia\Sql\Adapter\Statement\StatementInterface;
use PDO;
use RuntimeException;

/**
 * Encapsulates a PDO as a read-write adapter.
 */
class PdoWriteableAdapter implements PdoWriteableAdapterInterface
{

    /**
     * The used PDO instance.
     *
     * @var PDO
     */
    private $pdo = null;

    /**
     * Constructor.
     *
     * @param PDO $pdo
     *            The used PDO instance.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\ReadableAdapterInterface::prepare()
     */
    public function prepare(string $sql): StatementInterface
    {
        return new PdoStatement($this->pdo->prepare($sql), $sql);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\PdoReadableAdapterInterface::getPdo()
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\WriteableAdapterInterface::getLastInsertId()
     */
    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\WriteableAdapterInterface::startTransaction()
     */
    public function startTransaction()
    {
        if ($this->pdo->inTransaction()) {
            throw new RuntimeException('Transaction already started.');
        }

        $this->pdo->beginTransaction();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\WriteableAdapterInterface::inTransaction()
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\WriteableAdapterInterface::commitTransaction()
     */
    public function commitTransaction()
    {
        if (! $this->pdo->inTransaction()) {
            throw new RuntimeException('No transaction started.');
        }

        $this->pdo->commit();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Nia\Sql\Adapter\WriteableAdapterInterface::rollBackTransaction()
     */
    public function rollBackTransaction()
    {
        if (! $this->pdo->inTransaction()) {
            throw new RuntimeException('No transaction started.');
        }

        $this->pdo->rollBack();
    }
}
