<?php

class repose_ConfigurationProperty {
    protected $type = null;
    protected $name;
    protected $columnName;
    protected $isObject;
    protected $isPrimaryKey;
    public function __construct($name, $config) {
        $this->name = $name;
        $this->columnName = isset($config['columnName']) ? $config['columnName'] : $name;
        $this->type = isset($config['relationship']) ? $config['relationship'] : 'property';
        $this->isObject = $this->type === 'property' ? false : true;
        $this->isPrimaryKey = isset($config['primaryKey']) ? true : false;
    }
    public function getType() {
        return $this->type;
    }
    public function getName() {
        return $this->name;
    }
    public function getColumnName() {
        return $this->columnName;
    }
    public function isObject() {
        return $this->isObject;
    }
    public function isPrimaryKey() {
        return $this->isPrimaryKey;
    }
}

?>
