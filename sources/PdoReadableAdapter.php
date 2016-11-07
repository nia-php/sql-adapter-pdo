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

/**
 * Encapsulates a PDO as a readonly adapter.
 */
class PdoReadableAdapter implements PdoReadableAdapterInterface
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
}
