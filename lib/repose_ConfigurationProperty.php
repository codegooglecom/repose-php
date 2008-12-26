<?php

class repose_ConfigurationProperty {
    protected $configuration;
    protected $type = null;
    protected $name;
    protected $columnName;
    protected $isObject;
    protected $isPrimaryKey;
    protected $className;
    protected $foreignKey;
    public function __construct($configuration, $name, $config) {
        $this->configuration = $configuration;
        $this->name = $name;
        $this->type = isset($config['relationship']) ? $config['relationship'] : 'property';
        $this->isObject = $this->type === 'property' ? false : true;
        $this->isPrimaryKey = isset($config['primaryKey']) ? true : false;
        if ( $this->isObject ) {
            if ( ! isset($config['className']) ) {
                throw new Exception('Object relationship must have class name specified.');
            }
            $this->className = $config['className'];
        } else {
            $this->className = null;
        }
        if ( isset($config['columnName']) ) {
            $this->columnName = $config['columnName'];
        }
        $this->foreignKey = isset($config['foreignKey']) ? $config['foreignKey'] : null;
    }
    public function getType() {
        return $this->type;
    }
    public function getName() {
        return $this->name;
    }
    public function getColumnName() {
        if ( $this->columnName === null ) {
            if ( $this->isObject ) {

                // do something to figure out column name based on object type!

                $config = $this->configuration->getForClass($this->className);

                $primaryKeyDetails = $config->getPrimaryKeyDetails();

                if ( $primaryKeyDetails['type'] == 'single' ) {
                    $this->columnName = $primaryKeyDetails['property']->getColumnName();
                } else {
                    throw new Exception('Unable to handle composite primary key relationships.');
                }
            }
            if ( $this->columnName === null ) {
                $this->columnName = $this->name;
            }
        }
        return $this->columnName;
    }
    public function getForeignKey() {
        if ( $this->foreignKey === null ) {
            if ( $this->isObject ) {
                $config = $this->configuration->getForClass($this->className);
                $primaryKeyDetails = $config->getPrimaryKeyDetails();
                if ( $primaryKeyDetails['type'] == 'single' ) {
                    $this->foreignKey = $primaryKeyDetails['property']->getColumnName();
                } else {
                    throw new Exception('Unable to handle composite primary key relationships.');
                }
            }
            if ( $this->foreignKey === null ) {
                $this->foreignKey = $this->getColumnName();
            }
        }
        return $this->foreignKey;
    }
    public function isObject() {
        return $this->isObject;
    }
    public function isPrimaryKey() {
        return $this->isPrimaryKey;
    }
    public function getClassName() {
        return $this->className;
    }
    public function __destruct() {
        $this->configuration = null;
    }
}

?>
