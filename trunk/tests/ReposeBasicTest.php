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

                'sample_ProjectInfo' => array(
                    'tableName' => 'projectInfo',
                    'properties' => array(
                        'projectInfoId' => array( 'primaryKey' => 'true', ),
                        'description' => null,
                        'project' => array(
                            'relationship' => 'one-to-one',
                            'className' => 'sample_Project',
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
        $dataSource->exec('DROP TABLE IF EXISTS projectInfo');
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
CREATE TABLE projectInfo (
    projectInfoId INTEGER PRIMARY KEY AUTOINCREMENT,
    projectId INTEGER NOT NULL,
    description TEXT NOT NULL
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

        $dataSource->exec('INSERT INTO user (userId, name) VALUES (100001, "firstUser")');
        $dataSource->exec('INSERT INTO user (userId, name) VALUES (100002, "secondUser")');

        $dataSource->exec('INSERT INTO user (userId, name) VALUES (55566, "existingManager")');
        $dataSource->exec('INSERT INTO user (userId, name) VALUES (67387, "existingUser")');

        $dataSource->exec('INSERT INTO project (projectId, name, managerUserId) VALUES (12345, "Existing Project", 55566)');
        $dataSource->exec('INSERT INTO bug (bugId, title, body, projectId, reporterUserId, ownerUserId) VALUES (521152, "Existing Bug", "This bug existed from the time the database was created", 12345, 67387, 55566)');

    }

    public function testSimpleIdentity() {

        $this->loadClass('sample_User');

        $userBeau = new sample_User('beau');

        $userMapOne = array('user' => $userBeau);
        $userMapTwo = array('user' => $userBeau);

        $this->assertTrue($userMapOne['user'] === $userMapTwo['user']);


    }


    public function testSimpleArrayIdentity() {

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

        $this->assertTrue($bug->getOwner() === $bug->getProject()->getManager());

        $loadedBug = $this->getSession()->load('sample_Bug', $bug->getBugId());

        $this->assertTrue($bug === $loadedBug);

        $loadedNullBug = null;

        try {
            $loadedNullBug = $this->getSession()->load('sample_Bug', -1);
        } catch(Exception $e) {
        }

        $this->assertNull($loadedNullBug);

    }

    public function testLoadingExistingModel() {

        $userOne = $this->getSession()->load('sample_User', 100001);
        $userTwo = $this->getSession()->load('sample_User', 100002);

        $this->assertNotNull($userOne);
        $this->assertNotNull($userTwo);

    }

    public function testLoadExistingBug() {

        $bug = $this->getSession()->load('sample_Bug', 521152);

        $this->assertEquals('Existing Bug', $bug->getTitle());
        $this->assertEquals('This bug existed from the time the database was created', $bug->getBody());

        $this->assertEquals('existingUser', $bug->getReporter()->getName(), 'Reporter');
        $this->assertEquals('existingManager', $bug->getOwner()->getName(), 'Owner');

        $this->assertEquals('Existing Project', $bug->getProject()->getName(), 'Bug\'s Project\'s name does not match');
        $this->assertEquals('existingManager', $bug->getProject()->getManager()->getName(), 'Manager');

    }

    public function testLoadExistingProject() {

        $project = $this->getSession()->load('sample_Project', 12345);

        $this->assertEquals('Existing Project', $project->getName());
        $this->assertEquals('existingManager', $project->getManager()->getName(), 'Manager');

    }

    public function testSampleQueries() {

        $query = $this->getSession()->createQuery('FROM sample_Bug');
        $bugs = $query->execute();

        $this->assertEquals(1, count($bugs));

        $query = $this->getSession()->createQuery('FROM sample_Bug bug');
        $bugs = $query->execute();

        $this->assertEquals(1, count($bugs));

        $this->assertEquals("Existing Bug", $bugs[0]->getTitle());

        $query = $this->getSession()->createQuery('FROM sample_User user WHERE user.name = :name');
        $users = $query->execute(array('name' => 'existingManager'));

        $this->assertEquals(1, count($users));
        $this->assertEquals('existingManager', $users[0]->getName());

        $query = $this->getSession()->createQuery(
            'FROM sample_Bug bug WHERE bug.project.manager.userId = :userId'
        );

        $bugs = $query->execute(array('userId' => 55566));

        $this->assertEquals(1, count($bugs));

        $this->assertEquals("Existing Bug", $bugs[0]->getTitle());

        $query = $this->getSession()->createQuery(
            'SELECT bug.project FROM sample_Bug bug WHERE bug.owner.userId = :userId'
        );

        $projects = $query->execute(array('userId' => 55566));

        $this->assertEquals("Existing Project", $projects[0]->getName());

    }

    public function testDelete() {

        $bug = $this->getSession()->load('sample_Bug', 521152);

        $this->getSession()->delete($bug);

        $newSession = $this->sessionFactory->openSession();

        $newBug = null;

        try {
            // We expect an exception here.
            $newBug = $newSession->load('sample_Bug', 521152);
        } catch (Exception $e) {
        }

        $this->assertTrue(null === $newBug, 'Bug should not have been loaded, it should have been deleted.');

    }

    public function testSessionFlushing() {

        $project = $this->getSession()->load('sample_Project', 12345);

        $project->setName('Updated Project');

        $newSessionOne = $this->sessionFactory->openSession();

        $projectOne = $newSessionOne->load('sample_Project', 12345);

        $this->assertNotEquals($project->getName(), $projectOne->getName());

        $this->getSession()->flush();

        $this->assertNotEquals($project->getName(), $projectOne->getName());

        $newSessionTwo = $this->sessionFactory->openSession();

        $projectTwo = $newSessionTwo->load('sample_Project', 12345);

        $this->assertEquals($project->getName(), $projectTwo->getName());

        $this->assertNotEquals($project->getName(), $projectOne->getName());

        $this->assertNotEquals($projectTwo->getName(), $projectOne->getName());

    }

}

?>
