<?php
require_once(REPOSE_LIB . 'repose_ConfigurationClass.php');
require_once(REPOSE_LIB . 'repose_SessionFactory.php');
class repose_Configuration {
    protected $classes = array();
    public function __construct($config) {
        foreach ( $config['classes'] as $className => $classConfig ) {
            $this->classes[$className] = new repose_ConfigurationClass($className, $classConfig);
        }
    }
    public function getForClass($clazz) {
        return $this->classes[$clazz];
    }
    public function buildSessionFactory() {
        return new repose_SessionFactory($this);
    }
}
?>
