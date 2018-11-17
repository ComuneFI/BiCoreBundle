<?php

namespace Cdf\BiCoreBundle\Utils\Database;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseUtils
{

    private $container;
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $this->container->get("doctrine")->getManager();
    }

    public function getFieldType($entity, $field)
    {
        $metadata = $this->em->getClassMetadata(get_class($entity));
        $fieldMetadata = $metadata->fieldMappings[$field];

        $fieldType = $fieldMetadata['type'];
        return $fieldType;
    }

    public function isRecordChanged($entity, $fieldname, $oldvalue, $newvalue)
    {
        $fieldtype = $this->getFieldType(new $entity(), $fieldname);
        if ($fieldtype === 'boolean') {
            return $oldvalue !== $newvalue;
        }
        if ($fieldtype === 'datetime' || $fieldtype === 'date') {
            return $this->isDateChanged($oldvalue, $newvalue);
        }
        if (is_array($oldvalue)) {
            return $this->isArrayChanged($oldvalue, $newvalue);
        }

        return ($oldvalue !== $newvalue);
    }

    public function isDateChanged($oldvalue, $newvalue)
    {
        $datenewvalue = new \DateTime();
        $datenewvalue->setTimestamp($newvalue);
        $twoboth = !$oldvalue && !$newvalue;
        if ($twoboth) {
            return false;
        }
        $onlyonenull = (!$oldvalue && $newvalue) || ($oldvalue && !$newvalue);
        if ($onlyonenull) {
            return true;
        }
        $changed = ($oldvalue != $datenewvalue);
        return $changed;
    }

    public function isArrayChanged($oldvalue, $newvalue)
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

    public function truncateTable($entityclass, $cascade = false)
    {
        $cmd = $this->em->getClassMetadata($entityclass);
        $connection = $this->em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $dbtype = $connection->getDriver()->getDatabasePlatform()->getName();
        $retval = false;

        switch ($dbtype) {
            case 'mysql':
                $connection->query('SET FOREIGN_KEY_CHECKS=0');
                $q = $dbPlatform->getTruncateTableSql($cmd->getTableName(), $cascade);
                $retval = $connection->executeUpdate($q);
                $connection->query('SET FOREIGN_KEY_CHECKS=1');
                break;
            case 'postgresql':
                $cascadesql = $cascade ? "CASCADE" : "";
                $retval = $connection->executeQuery(sprintf('TRUNCATE TABLE %s ' . $cascadesql, $cmd->getTableName()));
                break;
            default:
                $q = $dbPlatform->getTruncateTableSql($cmd->getTableName(), $cascade);
                $retval = $connection->executeUpdate($q);
                break;
        }
        $this->em->clear();
        return $retval;
    }

    public function isSchemaChanged()
    {
        $kernel = $this->container->get("kernel");
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--no-debug' => true,
            '--env' => $kernel->getEnvironment(),
        ));

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        $changed = strpos($content, 'Nothing to update');
        return ($changed !== 0);
    }
}
