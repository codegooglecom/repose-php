<?php

class sample_Project {
    protected $projectId;
    protected $name;
    protected $manager;
    public function __construct($name, $manager) {
        $this->name = $name;
        $this->manager = $manager;
    }
    public function getProjectId() {
        return $this->projectId;
    }
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getManager() {
        return $this->manager;
    }
    public function setManager($manager) {
        $this->manager = $manager;
    }
}

?>
