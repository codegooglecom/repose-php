<?php

class repose_ProxyGenerator {
    static private $PROXIES_LOADED = array();
    static private $PROXY_REPOSE_SESSION_PROPERTY_NAME = '___reposeSession';
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
    private function assertProxyExists($object) {
        if ( $object instanceof repose_IProxy ) return;
        $clazz = $this->getProxyClassName($object);
        if ( array_key_exists($clazz, self::$PROXIES_LOADED) ) return;
        eval($this->generateProxy($object));
        self::$PROXIES_LOADED[$clazz] = true;
    }
    // TODO Temporary hack!
    private $cache;
    private function castAsProxyObject($object, $session) {
        $this->assertProxyExists($object);
        $clazz = $this->getProxyClassName($object);
        $class = new ReflectionClass($clazz);
        $serialized = serialize($object);
        if ( isset($this->cache[$serialized]) ) return $this->cache[$serialized];
        $proxy = $class->newInstance();
        $proxy->___reposeProxySetter(self::$PROXY_REPOSE_SESSION_PROPERTY_NAME, $session);
        $proxy->___reposeProxyClone($object);
        return $this->cache[$serialized] = $proxy;
    }
    public function getProxyObject($object, $session) {
        // TODO: We might want to ensure that we pass back the proxy for
        // this particular session?
        if ( $object instanceof repose_IProxy ) return $object;
        return $this->castAsProxyObject($object, $session);
    }
    public function getProxyObjectFromData($clazz, $data, $session) {
        $this->assertProxyExists($clazz);
        $clazz = $this->getProxyClassName($clazz);
        $class = new ReflectionClass($clazz);
        $proxy = $class->newInstance();
        $proxy->___reposeProxySetter(self::$PROXY_REPOSE_SESSION_PROPERTY_NAME, $session);
        $proxy->___reposeProxyFromData($data);
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
        $c .= '    protected $' . self::$PROXY_REPOSE_SESSION_PROPERTY_NAME . ';' . "\n";
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
        $c .= '    public function ___reposeProxyFromData($data) {' . "\n";
        $c .= '        foreach ( $this->___reposeProxyGetProperties() as $prop ) {' . "\n";
        $c .= '            $value = $data[$prop->getName()];' . "\n";
        $c .= '            if ( $value !== null and $prop->isObject() ) {' . "\n";
        $c .= '                $value = $this->' . self::$PROXY_REPOSE_SESSION_PROPERTY_NAME . '->castAsProxy($value);' . "\n";
        $c .= '            }' . "\n";
        $c .= '            $this->___reposeProxySetter($prop->getName(), $value);' . "\n";
        $c .= '        }' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyClone($source) {' . "\n";
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
        $c .= '        foreach ( $this->___reposeProxyGetProperties() as $prop ) {' . "\n";
        $c .= '            $value = $source->___reposeProxyGetter($prop->getName());' . "\n";
        $c .= '            if ( $value !== null and $prop->isObject() ) {' . "\n";
        $c .= '                $value = $this->' . self::$PROXY_REPOSE_SESSION_PROPERTY_NAME . '->castAsProxy($value);' . "\n";
        $c .= '            }' . "\n";
        $c .= '            $this->___reposeProxySetter($prop->getName(), $value);' . "\n";
        $c .= '        }' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyPrimaryKey() {' . "\n";
        $c .= '        $primaryKeyDetails = $this->' . self::$PROXY_REPOSE_SESSION_PROPERTY_NAME . '->getClassConfig($this)->getPrimaryKeyDetails();' . "\n";
        $c .= '        switch($primaryKeyDetails[\'type\']) {' . "\n";
        $c .= '            case \'single\':' . "\n";
        $c .= '                return $this->___reposeProxyGetter($primaryKeyDetails[\'propertyName\']);' . "\n";
        $c .= '                break;' . "\n";
        $c .= '            case \'composite\':' . "\n";
        $c .= '                break;' . "\n";
        $c .= '        }' . "\n";
        $c .= '    }' . "\n";
        $c .= '    public function ___reposeProxyGetProperties() {' . "\n";
        $c .= '        return $this->' . self::$PROXY_REPOSE_SESSION_PROPERTY_NAME . '->getClassConfig($this)->getProperties();' . "\n";
        $c .= '    }' . "\n";
        $c .= '}' . "\n";
echo $c . "\n\n";
        return $c;

    }

}

?>
