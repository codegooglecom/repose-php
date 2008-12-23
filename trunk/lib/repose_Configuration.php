<?php
require_once(REPOSE_LIB . 'repose_ConfigurationClass.php');
require_once(REPOSE_LIB . 'repose_SessionFactory.php');
class repose_Configuration {
    private $dataSource = null;
    private $dataSourceConfig = array(
        'dsn' => null,
        'username' => null,
        'password' => null,
        'driver' => null,
    );
    protected $classes = array();
    protected $autoload = null;
    public function __construct($config) {
        foreach ( $config['classes'] as $className => $classConfig ) {
            $this->classes[$className] = new repose_ConfigurationClass($this, $className, $classConfig);
        }
        if ( isset($config['connection']['dataSource']) ) {
            $this->dataSource = $config['connection']['dataSource'];
            // TODO Get config information.
        } elseif ( isset($config['connection']['dsn']) ) {
            foreach ( array('dsn', 'username', 'password') as $dataSourceConfigKey ) {
                $this->dataSourceConfig[$dataSourceConfigKey] = $config['connection'][$dataSourceConfigKey];
            }
            $dsnParts = explode(':', $this->dataSourceConfig['dsn']);
            if ( count($dsnParts) ) {
                $this->dataSourceConfig['driver'] = $dsnParts[0];
            }
        } else {
            switch($config['connection']['driver']) {
                case 'sqlite':
                    $this->dataSourceConfig['dsn'] = 'sqlite:' . $config['connection']['filename'];
                    $this->dataSourceConfig['driver'] = 'sqlite';
                    break;
                default:

                    foreach ( array('username', 'password', 'driver') as $dataSourceConfigKey ) {
                        $this->dataSourceConfig[$dataSourceConfigKey] =
                            isset($config['connection'][$dataSourceConfigKey]) ?
                                $config['connection'][$dataSourceConfigKey] :
                                null;
                    }

                    $this->dataSourceConfig['dsn'] = sprintf(
                        '%s:dbname=%s;host=%s',
                        isset($config['connection']['driver']) ?
                            $config['connection']['driver'] : 'mysql',
                        isset($config['connection']['dbName']) ?
                            $config['connection']['dbName'] : null,
                        isset($config['connection']['hostname']) ?
                            $config['connection']['hostname'] : null
                    );

                    break;
            }

        }
        if ( isset($config['autoload']) ) {
            $this->autoload = $config['autoload'];
        }
    }
    public function getForClass($clazz) {
        return $this->classes[$clazz];
    }
    public function buildSessionFactory() {
        return new repose_SessionFactory($this);
    }
    public function getDataSource() {
        if ( $this->dataSource === null ) {
            $this->dataSource = new PDO(
                $this->dataSourceConfig['dsn'],
                $this->dataSourceConfig['username'],
                $this->dataSourceConfig['password']
            );
        }
        return $this->dataSource;
    }
    public function loadClass($clazz) {
        if ( ! class_exists($clazz) ) {
            echo " [ should try to load class $clazz ]\n";
            if ( $this->autoload !== null ) {
                call_user_func_array($this->autoload, array($clazz));
            }
        }
    }
    public function __destruct() {
        $this->classes = null;
    }
}
?>
