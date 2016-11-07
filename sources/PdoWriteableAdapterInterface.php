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

/**
 * Interface for implementations to encapsulates a PDO as a read-write adapter.
 */
interface PdoWriteableAdapterInterface extends PdoReadableAdapterInterface, WriteableAdapterInterface
{
}
