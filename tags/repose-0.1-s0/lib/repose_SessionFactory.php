<?php

require_once(REPOSE_LIB . 'repose_Configuration.php');
require_once(REPOSE_LIB . 'repose_Session.php');

class repose_SessionFactory {
    private $configuration;
    private $currentSession;
    public function __construct(repose_Configuration $configuration) {
        $this->configuration = $configuration;
        $this->currentSession = new repose_Session($this->configuration);
    }
    public function getCurrentSession() {
        return $this->currentSession;
    }
    public function openSession() {
        return new repose_Session($this->configuration);
    }
}

?>
