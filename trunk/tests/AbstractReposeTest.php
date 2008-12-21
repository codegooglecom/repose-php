<?php

require_once 'PHPUnit/Framework.php';

require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'repose.php');

abstract class AbstractReposeTest extends PHPUnit_Framework_TestCase {
    
    public function loadClass($clazz) {
        if ( ! class_exists($clazz) ) {
            $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . $clazz . '.php';
            require_once($filename);
        }
    }

}

?>
