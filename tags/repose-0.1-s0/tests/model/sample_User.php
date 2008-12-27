<?php

class sample_User {
    protected $userId;
    protected $name;
    public function __construct($name) {
        $this->name = $name;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
}

?>
