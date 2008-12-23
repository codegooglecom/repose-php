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

            'connection' => array( 'dsn' => 'sqlite:' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ReposeBasicTest.sq3', ),

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

    public function testSimpleIdentity() {

        $this->loadClass('sample_User');

        $userBeau = new sample_User('beau');

        $userMapOne = array('user' => $userBeau);
        $userMapTwo = array('user' => $userBeau);

        $this->assertTrue($userMapOne['user'] === $userMapTwo['user']);


    }


    public function testSimpleArrayIdentity() {

        $this->loadClass('sample_User');

        $userBeau = new sample_User('beau');
        $userDummy = new sample_User('beau');

        $usersOnlyBeau = array($userBeau);

        $this->assertFalse($userBeau === $userDummy);

        $this->assertTrue(in_array($userBeau, $usersOnlyBeau, true));
        $this->assertFalse(in_array($userDummy, $usersOnlyBeau, true));

    }

    public function testSimpleModelUsageWithoutPersistence() {

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

        $this->assertEquals('Something is broken', $bug->getTitle());
        $this->assertEquals('Click http://example.com/ to test!', $bug->getBody());

        $this->assertEquals('josh', $bug->getReporter()->getName(), 'Reporter');
        $this->assertEquals('beau', $bug->getOwner()->getName(), 'Owner');

        $this->assertEquals('Sample Project', $bug->getProject()->getName(), 'Bug\'s Project\'s name does not match');
        $this->assertEquals('beau', $bug->getProject()->getManager()->getName(), 'Manager');

        $this->assertEquals('Sample Project', $project->getName());
        $this->assertEquals('beau', $project->getManager()->getName(), 'Manager');

        $this->assertEquals('beau', $userBeau->getName());
        $this->assertEquals('josh', $userJosh->getName());

        $this->assertTrue($bug->getProject()->getManager() === $bug->getOwner());

        $bug = $this->getSession()->save($bug);

        try {
            $this->getSession()->save($bug);
            $this->assertTrue(false, "Resaving bug should have thrown an exception");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }

        $bug->getOwner()->setName("beau updated");

        $this->getSession()->update($bug);

    }

}

?>
