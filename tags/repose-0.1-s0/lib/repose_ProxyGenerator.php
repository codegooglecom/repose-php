<?php

class repose_ProxyGenerator {
    static private $PROXIES_LOADED = array();
    public function getProxyClassName($object) {
        $clazz = null;
        if ( is_object($object) ) {
            $clazz = get_class($object);
        } else {
            $clazz = $object;
        }
        if ( preg_match('/__ReposeProxy$/', $clazz) ) {
            throw new Exception('Cannot create a proxy of a proxy! (tried to get proxy class name for "' . $clazz. '")');
        }
        return $clazz . '__ReposeProxy';
    }
    private function assertProxyExists($object, $session) {
        if ( $object instanceof repose_IProxy ) return;
        $clazz = $this->getProxyClassName($object);
        if ( array_key_exists($clazz, self::$PROXIES_LOADED) ) return;
        if ( ! is_object($object) ) {
            $session->assertClassLoaded($object);
        }
        eval($this->generateProxy($object));
        self::$PROXIES_LOADED[$clazz] = true;
    }
    // TODO Should make sure to flush cache when session closes!
    // TODO Add __dstruct method.
    private $cache = array();
    private function castAsProxyObject($object, $session) {
        $this->assertProxyExists($object, $session);
        $originalClass = get_class($object);
        if ( ! isset($this->cache[$originalClass]) ) {
            $this->cache[$originalClass] = array();
        }
        foreach ( $this->cache[$originalClass] as $cachedItem ) {
            if ( $cachedItem['originalObject'] == $object ) {
                return $cachedItem['proxyObject'];
            }
        }
        $clazz = $this->getProxyClassName($object);
        $class = new ReflectionClass($clazz);
        $proxy = $class->newInstance();
        $proxy->___reposeProxyClone($session, $object);
        $this->cache[$originalClass][] = array(
            'originalObject' => $object,
            'proxyObject' => $proxy
        );
        return $proxy;
    }
    public function getProxyObject($object, $session) {
        // TODO: We might want to ensure that we pass back the proxy for
        // this particular session?
        if ( $object instanceof repose_IProxy ) return $object;
        return $this->castAsProxyObject($object, $session);
    }
    public function getProxyObjectFromData($clazz, $data, $session) {
        $this->assertProxyExists($clazz, $session);
        $clazz = $this->getProxyClassName($clazz);
        $class = new ReflectionClass($clazz);
        $proxy = $class->newInstance();
        $proxy->___reposeProxyFromData($session, $data);
        return $proxy;
    }
    private function generateProxy($object) {

        $clazz = null;

        if ( is_object($object) ) {
            $clazz = get_class($object);
        } else {
            $clazz = $object;
        }

        $proxyClazz = $this->getProxyClassName($object);

        $c = '';

        $c .= 'class ' . $proxyClazz . ' extends ' . $clazz . ' implements repose_IProxy {' . "\n";
        $c .= '    // Noarg constructor ensures we can instantiate this object raw.' . "\n";
        $c .= '    public function __construct() {' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyOriginalClassName() {' . "\n";
        $c .= '        return \'' . $clazz . '\';' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxySetter($prop, $value = null) {' . "\n";
        $c .= '        $this->$prop = $value;' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyGetter($prop) {' . "\n";
        $c .= '        return $this->$prop;' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyColumnData($session) {' . "\n";
        $c .= '        $data = array();' . "\n";
        $c .= '        foreach ( $this->___reposeProxyGetProperties($session) as $prop ) {' . "\n";
        $c .= '            $value = $this->___reposeProxyGetter($prop->getName());' . "\n";
        $c .= '            if ( $prop->isObject() and $value !== null ) {' . "\n";
        $c .= '                $value = $value->___reposeProxyPrimaryKey($session);' . "\n";
        $c .= '            }' . "\n";
        $c .= '            $data[$prop->getColumnName()] = $value;' . "\n";
        $c .= '        }' . "\n";
        $c .= '        return $data;' . "\n";
        $c .= '    }' . "\n";
        /*
        $c .= '    public function ___reposeProxyData($session) {' . "\n";
        $c .= '        $data = array();' . "\n";
        $c .= '        foreach ( $this->___reposeProxyGetProperties($session) as $prop ) {' . "\n";
        $c .= '            $value = $this->___reposeProxyGetter($prop->getName());' . "\n";
        $c .= '            if ( $prop->isObject() and $value !== null ) {' . "\n";
        $c .= '                $value = $value->___reposeProxyPrimaryKey($session);' . "\n";
        $c .= '            }' . "\n";
        $c .= '            $data[$prop->getName()] = $value;' . "\n";
        $c .= '        }' . "\n";
        $c .= '        return $data;' . "\n";
        $c .= '    }' . "\n";
        */
        $c .= '    public function ___reposeProxyFromData($session, $data) {' . "\n";
        $c .= '        foreach ( $this->___reposeProxyGetProperties($session) as $prop ) {' . "\n";
        $c .= '            $value = isset($data[$prop->getName()]) ? $data[$prop->getName()] : null;' . "\n";
        $c .= '            if ( $value !== null and $prop->isObject() ) {' . "\n";
        $c .= '                $value = $session->castAsProxy($value);' . "\n";
        $c .= '            }' . "\n";
        $c .= '            $this->___reposeProxySetter($prop->getName(), $value);' . "\n";
        $c .= '        }' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyClone($session, $source) {' . "\n";
        $c .= '        if ( ! ( $source instanceof repose_IProxy ) ) {' . "\n";
        $c .= '            // Hack to turn our source into a proxy without actually' . "\n";
        $c .= '            // calling a constructor.' . "\n";
        $c .= '            $serializedParts = explode(\':\', serialize($source));' . "\n";
        $c .= '            $serializedParts[1] = ' . strlen($proxyClazz) . ';' . "\n";
        $c .= '            $serializedParts[2] = \'"\' . \'' . $proxyClazz . '\' . \'"\';' . "\n";
        $c .= '            $source = unserialize(implode(\':\', $serializedParts));' . "\n";
        $c .= '        }' . "\n";
        $c .= '        // Once we are certain that our source is a proxy, we can' . "\n";
        $c .= '        // leverage the property getters.' . "\n";
        $c .= '        foreach ( $this->___reposeProxyGetProperties($session) as $prop ) {' . "\n";
        $c .= '            $value = $source->___reposeProxyGetter($prop->getName());' . "\n";
        $c .= '            if ( $value !== null and $prop->isObject() ) {' . "\n";
        $c .= '                $value = $session->castAsProxy($value);' . "\n";
        $c .= '            }' . "\n";
        $c .= '            $this->___reposeProxySetter($prop->getName(), $value);' . "\n";
        $c .= '        }' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyPrimaryKey($session) {' . "\n";
        $c .= '        $primaryKeyDetails = $session->getClassConfig($this)->getPrimaryKeyDetails();' . "\n";
        $c .= '        switch($primaryKeyDetails[\'type\']) {' . "\n";
        $c .= '            case \'single\':' . "\n";
        $c .= '                return $this->___reposeProxyGetter($primaryKeyDetails[\'propertyName\']);' . "\n";
        $c .= '                break;' . "\n";
        $c .= '            case \'composite\':' . "\n";
        $c .= '                break;' . "\n";
        $c .= '        }' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyGetProperties($session) {' . "\n";
        $c .= '        return $session->getClassConfig($this)->getProperties();' . "\n";
        $c .= '    }' . "\n";
        $c .= '}' . "\n";
//echo $c . "\n\n";
        return $c;

    }

}

?>
