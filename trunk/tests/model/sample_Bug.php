<?php

class sample_Bug {
    protected $bugId;
    protected $title;
    protected $body;
    protected $projectId;
    protected $reporter;
    protected $owner;
    public function __construct($project, $title, $body, $reporter, $owner = null) {
        $this->project = $project;
        $this->title = $title;
        $this->body = $body;
        $this->reporter = $reporter;
        $this->owner = $owner;
    }
    public function getBugId() {
        return $this->bugId;
    }
    public function getProject() {
        return $this->project;
    }
    public function setProject($project) {
        $this->project = $project;
    }
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($title) {
        $this->title = $title;
    }
    public function getBody() {
        return $this->body;
    }
    public function setBody($body) {
        $this->body = $body;
    }
    public function getReporter() {
        return $this->reporter;
    }
    public function setReporter($reporter) {
        $this->reporter = $reporter;
    }
    public function getOwner() {
        return $this->owner;
    }
    public function setOwner($owner) {
        $this->owner = $owner;
    }
}

?>
