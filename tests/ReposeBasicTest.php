<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AbstractReposeTest.php');

class ReposeBasicTest extends AbstractReposeTest {

    private $sessionFactory;

    public function getSession() {
        return $this->sessionFactory->getCurrentSession();
    }

    public function setUp() {

        $configuration = new repose_Configuration(array(

            'autoload' => array($this, 'loadClass'),

            'connection' => array( 'dsn' => 'sqlite:ReposeBasicTest.sq3', ),

            'classes' => array(

                'sample_Project' => array(
                    'tableName' => 'project',
                    'properties' => array(
                        'projectId' => array( 'primaryKey' => 'true', ),
                        'name' => null,
                        'manager' => array(
                            'relationship' => 'many-to-one',
                            'className' => 'sample_User',
                            'columnName' => 'managerUserId',
                            //'foreignKey' => 'userId', // should get this itself!
                        ),
                    ),
                ),

                'sample_Bug' => array(
                    'tableName' => 'bug',
                    'properties' => array(
                        'bugId' => array( 'primaryKey' => 'true', ),
                        'title' => null,
                        'body' => null,
                        'project' => array(
                            'relationship' => 'many-to-one',
                            'className' => 'sample_Project',
                            //'columnName' => 'projectId', // should get this itself!
                            //'foreignKey' => 'userId', // should get this itself!
                        ),
                        'reporter' => array(
                            'relationship' => 'many-to-one',
                            'className' => 'sample_User',
                            'columnName' => 'reporterUserId',
                            //'foreignKey' => 'userId', // should get this itself!
                        ),
                        'owner' => array(
                            'relationship' => 'many-to-one',
                            'className' => 'sample_User',
                            'columnName' => 'ownerUserId',
                            //'foreignKey' => 'userId', // should get this itself!
                        ),
                    ),
                ),

                'sample_User' => array(
                    'tableName' => 'user',
                    'properties' => array(
                        'userId' => array( 'primaryKey' => 'true', ),
                        'name' => null,
                    ),
                ),

            ),
        ));

        $this->sessionFactory = $configuration->buildSessionFactory();

        $dataSource = $configuration->getDataSource();

        $dataSource->exec('DROP TABLE IF EXISTS user');
        $dataSource->exec('DROP TABLE IF EXISTS project');
        $dataSource->exec('DROP TABLE IF EXISTS bug');

        $dataSource->exec('
CREATE TABLE user (
    userId INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
)
');

        $dataSource->exec('
CREATE TABLE project (
    projectId INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    managerUserId INTEGER NOT NULL
)
');

        $dataSource->exec('
CREATE TABLE bug (
    bugId INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    body TEXT NOT NULL,
    projectId INTEGER NOT NULL,
    reporterUserId INTEGER NOT NULL,
    ownerUserId INTEGER
)
');

    }

    public function testModelSimple() {

        $this->loadClass('sample_User');
        $this->loadClass('sample_Project');
        $this->loadClass('sample_Bug');

        $userBeau = new sample_User('beau');
        $userJosh = new sample_User('josh');

        $project = new sample_Project('Sample Project', $userBeau);
        $bug = new sample_Bug(
            $project,
            'Something is broken',
            'Click http://example.com/ to test!',
            $userJosh, // Reporter
            $userBeau // Owner
        );

    }

}

?>
