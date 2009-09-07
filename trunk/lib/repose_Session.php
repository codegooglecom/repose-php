<?php

require_once('repose_ProxyGenerator.php');
require_once('repose_IProxy.php');
require_once('repose_Configuration.php');
require_once('repose_Query.php');

class repose_Session {

    /**
      * Proxy Generator used to generate proxy objects.
      */
    private $proxyGenerator;

    /**
      * Configuration.
      */
    private $configuration;

    /**
      * Proxy cache.
      */
    private $proxyCache = array();

    public function __construct(repose_Configuration $configuration = null) {
        $this->proxyGenerator = new repose_ProxyGenerator($configuration);
        $this->configuration = $configuration;
    }

    private function instantiateProxyObject($clazz, $data) {
        $object = $this->proxyGenerator->getProxyObjectFromData($clazz, $data, $this);
        return $object;
    }

    public function setFromData($clazz, $data) {
        return $this->set($this->instantiateProxyObject($clazz, $data));
    }

    public function set($object) {
        return $this->storeProxyObject($this->castAsProxy($object));
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
                    $value = $this->saveOrUpdateInternal($value)->___reposeProxyPrimaryKey($this);
                }
                $nonPkPropertyValues[$property->getColumnName()] = $value;
            }
        }
        return $nonPkPropertyValues;

    }

    /**
      * Save an object. Requires primary key to not be set. If non-NULL primary key, an
      * exception is thrown.
      * @param repose_IProxy|$object Object to save
      */
    public function save($object, $forceNew = false) {
        $this->flush();
        return $this->saveInternal($object, $forceNew);
    }
    private function saveInternal($object, $forceNew) {

        $object = $this->proxyGenerator->getProxyObject($object, $this, $forceNew);

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

        if ( $primaryKeyDetails['type'] == 'single' ) {
            $property = $primaryKeyDetails['property'];
            $object->___reposeProxySetter(
                $property->getName(),
                $nonPkPropertyValues[$property->getColumnName()] = $this->configuration->getDataSource()->lastInsertId()
            );
        }

        $this->storeProxyObject($object);

        //print_r($nonPkPropertyValues);
        return $object;
    }

    private function getUpdatedFields($object, $nonPkPropertyValues) {
        $data = array();
        $originalData = $this->proxyCache[$object->___reposeProxyOriginalClassName()][$object->___reposeProxyPrimaryKey($this)]['data'];
        foreach ( $nonPkPropertyValues as $propertyName => $value ) {
            if ( $originalData[$propertyName] !== $value ) {
                $data[$propertyName] = $value;
            }
        }
        return $data;
    }

    /**
      * Update an object. Requires primary key to be set. If NULL primary key, an
      * exception is thrown.
      * @param repose_IProxy|$object Object to update
      */
    public function update($object) {
        $this->flush();
        return $this->updateInternal($object);
    }
    private function updateInternal($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        if ( $object->___reposeProxyPrimaryKey($this) === null ) {
            throw new Exception("Cannot update object who has not already been saved (primary key is not set)");
        }
        $nonPkPropertyValues = $this->cascadeSaveOrUpdateGetValues($object);
        $updatedFields = $this->getUpdatedFields($object, $nonPkPropertyValues);
        // If there are no fields that need to be updated, we can just pass back
        // the object right away.
        if ( count($updatedFields) < 1 ) { return $object; }
        $classConfig = $this->getClassConfig($object);
        $query = 'UPDATE ' . $classConfig->getTableName() . ' SET ';
        $queryColumns = array();
        $queryValues = array();
        foreach ( $updatedFields as $columnName => $value ) {
            $queryColumns[] = $columnName . ' = :' . $columnName;
            $queryValues[$columnName] = $value;
        }
        $query .= implode(', ', $queryColumns);
        $primaryKeyDetails = $classConfig->getPrimaryKeyDetails();
        $query .= ' WHERE ' . $primaryKeyDetails['property']->getColumnName() . ' = :' . $primaryKeyDetails['property']->getColumnName();
        $queryValues[$primaryKeyDetails['property']->getColumnName()] = $object->___reposeProxyGetter( $primaryKeyDetails['property']->getName() );
        $statement = $this->configuration->getDataSource()->prepare($query);
        $statement->execute($queryValues);
        $this->storeProxyObject($object);
        return $object;
    }

    /**
      * Save or pdate an object. If object has a non-NULL primary key, {@link update()} is called.
      * Otherwise, {@link save()} is called.
      * @param repose_IProxy|$object Object to save or update
      */
    public function saveOrUpdate($object) {
        $this->flush();
        return $this->saveOrUpdateInternal($object);
    }
    private function saveOrUpdateInternal($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        if ( $object->___reposeProxyPrimaryKey($this) === null ) {
            return $this->saveInternal($object, false);
        } else {
            return $this->updateInternal($object);
        }
    }

    private function storeProxyObject($object) {
        if ( ! $object instanceof repose_IProxy ) {
            throw new Exception('Cannot store an object that is not a proxy!');
        }
        $clazz = $object->___reposeProxyOriginalClassName();
        if ( ! isset($this->proxyCache[$clazz]) ) {
            $this->proxyCache[$clazz] = array();
        }
        $primaryKeyValue = $object->___reposeProxyPrimaryKey($this);
        $this->proxyCache[$clazz][$primaryKeyValue] = array(
            'object' => $object,
            'data' => $object->___reposeProxyColumnData($this)
        );
        return $object;
    }

    public function setProxyProperty(repose_IProxy $object, $property, $value = null) {
        $clazz = $object->___reposeProxyOriginalClassName();
        $primaryKeyValue = $object->___reposeProxyPrimaryKey($this);
        $prop = $this->getClassConfig($object)->getProperty($property);
        $f = $this->proxyCache[$clazz][$primaryKeyValue]['data'][$prop->getColumnName()] =
            is_object($value) ? $value->___reposeProxyPrimaryKey($this) : $value;
    }

    public function createQuery($queryString) {
        return new repose_Query($this, $queryString);
    }

    public function load($clazz, $primaryKeyValue, $getIfNotExists = true) {
        if ( ! isset($this->proxyCache[$clazz]) ) {
            $this->proxyCache[$clazz] = array();
        }
        if ( isset($this->proxyCache[$clazz][$primaryKeyValue]) ) {
            return $this->proxyCache[$clazz][$primaryKeyValue]['object'];
        } else {
            if ( $getIfNotExists ) {
                $primaryKeyDetails = $this->getClassConfig($clazz)->getPrimaryKeyDetails();

                $query = $this->createQuery('FROM ' . $clazz . ' entity WHERE entity.' . $primaryKeyDetails['property']->getName() . ' = :id');
                $entities = $query->execute(array('id' => $primaryKeyValue));
                if ( count($entities) == 1 ) {
                    return $entities[0];
                } else {
                    throw new Exception('Expected exactly 1 result, but received ' . count($entities));
                }
            }
        }
        return null;
    }

    public function getClassConfig($object) {
        $clazz = null;
        if ( is_object($object) ) {
            if ( $object instanceof repose_IProxy ) $clazz = $object->___reposeProxyOriginalClassName();
            else $clazz = get_class($object);
        } else {
            $clazz = $object;
        }
        return $this->configuration->getForClass($clazz);
    }

    public function getClassPropertyConfig($object, $propertyName) {
        $clazz = null;
        if ( is_object($object) ) {
            if ( $object instanceof repose_IProxy ) $clazz = $object->___reposeProxyOriginalClassName();
            else $clazz = get_class($object);
        } else {
            $clazz = $object;
        }
        return $this->configuration->getPropertyForClass($clazz, $propertyName);
    }

    public function castAsProxy($object) {
        return $this->proxyGenerator->getProxyObject($object, $this);
    }

    public function getDataSource() {
        return $this->configuration->getDataSource();
    }

    public function assertClassLoaded($clazz) {
        $this->configuration->loadClass($clazz);
    }

    public function delete($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        if ( $object->___reposeProxyPrimaryKey($this) === null ) {
            throw new Exception("Cannot delete object who has not already been saved (primary key is not set)");
        }
        $classConfig = $this->getClassConfig($object);
        $query = 'DELETE FROM ' . $classConfig->getTableName();
        $queryValues = array();
        $primaryKeyDetails = $classConfig->getPrimaryKeyDetails();
        $query .= ' WHERE ' . $primaryKeyDetails['property']->getColumnName() . ' = :' . $primaryKeyDetails['property']->getColumnName();
        $queryValues[$primaryKeyDetails['property']->getColumnName()] = $object->___reposeProxyGetter( $primaryKeyDetails['property']->getName() );
        $statement = $this->configuration->getDataSource()->prepare($query);
        $statement->execute($queryValues);
    }

    public function flush() {
        foreach ( $this->proxyCache as $clazz => $cacheInfo ) {
            foreach ( $this->proxyCache[$clazz] as $primaryKeyValue => $objectInfo ) {
                $this->saveOrUpdateInternal($objectInfo['object']);
            }
        }
    }

}

?>
