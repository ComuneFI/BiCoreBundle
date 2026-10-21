<?php

namespace Cdf\BiCoreBundle\Utils\Database;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use \Exception;
use function count;

class DatabaseUtils
{
    public function __construct(private readonly KernelInterface $kernel, private readonly EntityManagerInterface $em)
    {
    }

    /**
     *
     * @param mixed $entity
     * @return string
     */
    public function getFieldType($entity, string $field)
    {
        $classname = $entity::class;
        if ($classname == false) {
            throw new Exception("Entity class not found: " . $entity);
        }
        $metadata = $this->em->getClassMetadata($classname);
        $fieldMetadata = $metadata->fieldMappings[$field];

        return $fieldMetadata['type'];
    }

    /**
     *
     * @param mixed $entity
     * @param string $fieldname
     * @param mixed $oldvalue
     * @param mixed $newvalue
     * @return bool
     */
    public function isRecordChanged($entity, $fieldname, $oldvalue, $newvalue)
    {
        $fieldtype = $this->getFieldType(new $entity(), $fieldname);
        if ('boolean' === $fieldtype) {
            return $oldvalue !== $newvalue;
        }
        if ('datetime' === $fieldtype || 'date' === $fieldtype) {
            return $this->isDateChanged($oldvalue, $newvalue);
        }
        if (is_array($oldvalue)) {
            return $this->isArrayChanged($oldvalue, $newvalue);
        }

        return $oldvalue !== $newvalue;
    }

    /**
     *
     * @param mixed $oldvalue
     * @param mixed $newvalue
     */
    public function isDateChanged($oldvalue, $newvalue): bool
    {
        $datenewvalue = new DateTime();
        $datenewvalue->setTimestamp($newvalue);
        $twoboth = !$oldvalue && !$newvalue;
        if ($twoboth) {
            return false;
        }
        $onlyonenull = (!$oldvalue && $newvalue) || ($oldvalue && !$newvalue);
        if ($onlyonenull) {
            return true;
        }

        return $oldvalue != $datenewvalue;
    }

    /**
     *
     * @param mixed $oldvalue
     * @param mixed $newvalue
     */
    public function isArrayChanged($oldvalue, $newvalue): bool
    {
        $twoboth = !$oldvalue && !$newvalue;
        if ($twoboth) {
            return false;
        }
        $onlyonenull = (!$oldvalue && $newvalue) || ($oldvalue && !$newvalue);
        if ($onlyonenull) {
            return true;
        }
        $numdiff = array_diff($oldvalue, $newvalue);

        return count($numdiff) > 0;
    }

    /**
     *
     * @param string $entityclass
     * @param bool $cascade
     */
    public function truncateTable($entityclass, $cascade = false): int|string
    {
        $cmd = $this->em->getClassMetadata($entityclass);
        $connection = $this->em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        //$dbtype = $connection->getDriver()->getDatabasePlatform()->getName();
        $platform = $connection->getDatabasePlatform();
        $driver = '';
        if ($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            $driver = 'postgre';
        }
        if ($platform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform) {
            $driver = 'mysql';
        }
        $retval = false;

        switch ($driver) {
            case 'mysql':
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
                $q = $dbPlatform->getTruncateTableSql($cmd->getTableName(), $cascade);
                $retval = $connection->executeStatement($q);
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
                break;
            case 'postgre':
                $cascadesql = $cascade ? 'CASCADE' : '';
                $tablename = $cmd->getTableName();
                if ($cmd->getSchemaName()) {
                    $tablename = $cmd->getSchemaName() . '.' . $tablename;
                }
                $retval = $connection->executeStatement(sprintf('TRUNCATE TABLE %s ' . $cascadesql, $tablename));
                break;
            default:
                $q = $dbPlatform->getTruncateTableSql($cmd->getTableName(), $cascade);
                $retval = $connection->executeStatement($q);
                break;
        }
        $this->em->clear();

        return $retval;
    }

    public function isSchemaChanged(): bool
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--no-debug' => true,
            '--env' => $this->kernel->getEnvironment(),
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        $changed = strpos($content, 'Nothing to update');

        return 0 !== $changed;
    }
}
