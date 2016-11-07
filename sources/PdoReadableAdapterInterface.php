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

use PDO;

/**
 * Interface for implementations to encapsulates a PDO as a readonly adapter.
 */
interface PdoReadableAdapterInterface extends ReadableAdapterInterface
{

    /**
     * Returns the used PDO instance.
     *
     * @return PDO The used PDO instance.
     */
    public function getPdo(): PDO;
}
