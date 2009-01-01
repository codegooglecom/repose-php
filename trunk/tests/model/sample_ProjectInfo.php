<?php

class sample_ProjectInfo {
    protected $projectInfoId;
    protected $project;
    protected $description;
    public function __construct($description) {
        $this->description = $description;
    }
    public function getProjectInfoId() {
        return $this->projectInfoId;
    }
    public function getProject() {
        return $this->project;
    }
    public function getDescription() {
        return $this->description;
    }
    public function setDescription($description) {
        $this->description = $description;
    }
}

?>
