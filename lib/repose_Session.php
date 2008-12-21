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

    private $counter = 1;
    public function save($object) {
        $object = $this->proxyGenerator->getProxyObject($object, $this);
        $fieldsToUpdate = array();
        foreach ( $this->getClassConfig($object)->getProperties() as $property ) {
            $value = null;
            if ( $property->isPrimaryKey() ) {
                if ( $object->___reposeProxyPrimaryKey() === null ) {
                    $object->___reposeProxySetter($property->getName(), $fieldsToUpdate[$property->getName()] = $this->counter++);
                } else {
                    $fieldsToUpdate[$property->getName()] = $object->___reposeProxyPrimaryKey();
                }
            } else {
                $value = $object->___reposeProxyGetter($property->getName());
                if ( $property->isObject() and $value !== null ) {
                    // If this property is an object, and it is not null,
                    // we should save out that object and store its proxy.
                    $value = $this->save($value)->___reposeProxyPrimaryKey();
                }
                $fieldsToUpdate[$property->getName()] = $value;
            }
        }
        print_r($fieldsToUpdate);
        return $object;
    }
    public function update($object) {
    }
    public function saveOrUpdate($object) {
    }

    public function getClassConfig($object) {
        $clazz = null;
        if ( $object instanceof repose_IProxy ) $clazz = $object->___reposeProxyOriginalClassName();
        else $clazz = get_class($object);
        return $this->configuration->getForClass($clazz);
    }

    public function castAsProxy($object) {
        return $this->proxyGenerator->getProxyObject($object, $this);
        return $object;
    }

    public function flush() {
    }

}

?>
