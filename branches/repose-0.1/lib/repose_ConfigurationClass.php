<?php
require_once(REPOSE_LIB . 'repose_ConfigurationProperty.php');
class repose_ConfigurationClass {
    protected $configuration;
    protected $name;
    protected $tableName;
    protected $properties = array();
    protected $primaryKeyProperties = array();
    protected $primaryKeyDetails = array();
    public function __construct($configuration, $name, $config) {
        $this->configuration = $configuration;
        $this->name = $name;
        $this->tableName = $config['tableName'];
        foreach ( $config['properties'] as $propertyName => $propertyConfig ) {
            $configurationProperty = new repose_ConfigurationProperty($this->configuration, $propertyName, $propertyConfig);
            $this->properties[$propertyName] = $configurationProperty;
            if ( $configurationProperty->isPrimaryKey() ) {
                $this->primaryKeyProperties[$propertyName] = $configurationProperty;
            }
        }
        if ( count($this->primaryKeyProperties) == 1 ) {
            $primaryKeyPropertyNames = array_keys($this->primaryKeyProperties);
            $this->primaryKeyDetails = array(
                'type' => 'single',
                'propertyName' => $primaryKeyPropertyNames[0],
                'property' => $this->primaryKeyProperties[$primaryKeyPropertyNames[0]]
            );
        } elseif ( count($primaryKeyProperties) > 1 ) {
            $this->primaryKeyDetails = array(
                'type' => 'composite',
                'propertyNames' => array_keys($this->primaryKeyProperties),
                'properties' => $this->primaryKeyProperties,
            );
        }
    }
    public function getName() {
        return $this->name;
    }
    public function getTableName() {
        return $this->tableName;
    }
    public function getProperty($name) {
        return $this->properties[$name];
    }
    public function getPropertyNames() {
        return array_keys($this->properties);
    }
    public function getProperties() {
        return array_values($this->properties);
    }
    public function getPrimaryKeyDetails() {
        return $this->primaryKeyDetails;
    }
    public function __destruct() {
        $this->properties = null;
        $this->configuration = null;
    }
}

?>
