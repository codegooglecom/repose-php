<?php

require_once(REPOSE_LIB . 'repose_ProxyGenerator.php');
require_once(REPOSE_LIB . 'repose_IProxy.php');
require_once(REPOSE_LIB . 'repose_Configuration.php');

class repose_Session {

    private $proxyGenerator;

    private $configuration;

    public function __construct(repose_Configuration $configuration = null) {
        $this->proxyGenerator = new repose_ProxyGenerator($configuration);
        $this->configuration = $configuration;
    }

    private function instantiateProxyObject($clazz, $data) {
        $object = $this->proxyGenerator->getProxyObjectFromData($clazz, $data, $this);
        return $object;
    }

    private function cascadeSaveOrUpdateGetValues($object) {
        $nonPkPropertyValues = array();
        foreach ( $this->getClassConfig($object)->getProperties() as $property ) {
            $value = null;
            if ( ! $property->isPrimaryKey() ) {
                $value = $object->___reposeProxyGetter($property->getName());
                if ( $property->isObject() and $value !== null ) {
                    // If this property is an object, and it is not null,
                    // we should save out that object and store its proxy.
                    $value = $this->saveOrUpdate($value)->___reposeProxyPrimaryKey($this);
                }
                $nonPkPropertyValues[$property->getColumnName()] = $value;
            }
        }
        return $nonPkPropertyValues;

    }
    private $counter = 1;
    public function save($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        if ( $object->___reposeProxyPrimaryKey($this) !== null ) {
            throw new Exception("Cannot save object who has already been saved (primary key already set)");
        }
        $nonPkPropertyValues = $this->cascadeSaveOrUpdateGetValues($object);
        $classConfig = $this->getClassConfig($object);
        $primaryKeyDetails = $classConfig->getPrimaryKeyDetails();

        $queryColumns = array();
        $queryValues = array();
        $queryValueTags = array();
        foreach ( $nonPkPropertyValues as $columnName => $value ) {
            $queryValueTags[] = ':' . $columnName;
            $queryValues[$columnName] = $value;
        }
        $query = 'INSERT INTO ' . $classConfig->getTableName() . ' (' . implode(', ', array_keys($nonPkPropertyValues)) . ') VALUES (' . implode(', ', $queryValueTags) . ')';

        $statement = $this->configuration->getDataSource()->prepare($query);
        $statement->execute($queryValues);
        //echo "\n\n" . $query . "\n\n";
        //echo "\n\n" . implode(', ', $queryValues) . "\n\n";

        if ( $primaryKeyDetails['type'] == 'single' ) {
            $property = $primaryKeyDetails['property'];
            $object->___reposeProxySetter(
                $property->getName(),
                $nonPkPropertyValues[$property->getColumnName()] = $this->configuration->getDataSource()->lastInsertId()
            );
        }

        //print_r($nonPkPropertyValues);
        return $object;
    }
    public function update($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        if ( $object->___reposeProxyPrimaryKey($this) === null ) {
            throw new Exception("Cannot update object who has not already been saved (primary key is not set)");
        }
        $nonPkPropertyValues = $this->cascadeSaveOrUpdateGetValues($object);
        $classConfig = $this->getClassConfig($object);
        $query = 'UPDATE ' . $classConfig->getTableName() . ' SET ';
        $queryColumns = array();
        $queryValues = array();
        foreach ( $nonPkPropertyValues as $columnName => $value ) {
            $queryColumns[] = $columnName . ' = :' . $columnName;
            $queryValues[$columnName] = $value;
        }
        $query .= implode(', ', $queryColumns);
        //print_r($nonPkPropertyValues);
        $primaryKeyDetails = $classConfig->getPrimaryKeyDetails();
        $query .= ' WHERE ' . $primaryKeyDetails['property']->getColumnName() . ' = :' . $primaryKeyDetails['property']->getColumnName();
        $queryValues[$primaryKeyDetails['property']->getColumnName()] = $object->___reposeProxyGetter( $primaryKeyDetails['property']->getName() );
        //echo $query . "\n\n";
        //echo implode(', ', $queryValues) . "\n\n";
        $statement = $this->configuration->getDataSource()->prepare($query);
        $statement->execute($queryValues);
        return $object;
    }
    public function saveOrUpdate($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        if ( $object->___reposeProxyPrimaryKey($this) === null ) {
            return $this->save($object);
        } else {
            return $this->update($object);
        }
    }

    public function getClassConfig($object) {
        $clazz = null;
        if ( $object instanceof repose_IProxy ) $clazz = $object->___reposeProxyOriginalClassName();
        else $clazz = get_class($object);
        return $this->configuration->getForClass($clazz);
    }

    public function castAsProxy($object) {
        return $this->proxyGenerator->getProxyObject($object, $this);
    }

    public function flush() {
    }

}

?>
