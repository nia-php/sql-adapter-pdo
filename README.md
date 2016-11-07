# nia - SQL Adapter PDO

Implementation of the `nia/sql-adapter` component using PDO. The implementation allows you to separate your PDO connection into a read and a write connection.

## Installation

Require this package with Composer.

```bash
composer require nia/sql-adapter
```
## Tests
To run the unit test use the following command:

```bash
$ cd /path/to/nia/component/
$ phpunit --bootstrap=vendor/autoload.php tests/
```

## How to use
The following sample shows you how to create a simple service provider (based on the `nia/dependencyinjection` component) which registers a reading SQL adapter and a writing SQL adapter.

```php
/**
 * Sample provider for sql connection adapters.
 */
class SqlAdapterProvider implements ProviderInterface
{

    /**
     *
     * {@inheritDoc}
     *
     * @see \Nia\DependencyInjection\Provider\ProviderInterface::register()
     */
    public function register(ContainerInterface $container)
    {
        // adapter for the read-only database servers (mostly a load balancer with multiple slave servers of a master-slave-replication).
        $readableAdapterFactory = new SharedFactory(new ClosureFactory(function (ContainerInterface $container) {
            $pdo = new PDO(/* ... */);

            return new PdoReadableAdapter($pdo);
        }));

        // adapter for the write-only database server (mostly the master of a master-slave-replication).
        $writeableAdapterFactory = new SharedFactory(new ClosureFactory(function (ContainerInterface $container) {
            $pdo = new PDO(/* ... */);

            return new PdoWriteableAdapter($pdo);
        }));

        $container->registerService(ReadableAdapterInterface::class, $readableAdapterFactory);
        $container->registerService(WriteableAdapterInterface::class, $writeableAdapterFactory);
    }
}


// somewhere in code: reading
// [...]
$statement = $container->get(ReadableAdapterInterface::class)->prepare('SELECT * FROM ...');
$statement->execute();

foreach ($statement as $row) {
    // [...]
}

// somewhere in code: writing
// [...]
$statement = $container->get(WriteableAdapterInterface::class)->prepare('UPDATE table SET field = :value;');
$statement->bind(':value', 'foobar');
$statement->execute();
```
