<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/max/Dal/tests/util/DalUnitTestCase.php';

/**
 * A class for testing non standard DataObjects_Accounts methods
 *
 * @package    MaxDal
 * @subpackage TestSuite
 *
 */
class DataObjects_AccoutnsTest extends DalUnitTestCase
{
    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function tearDown()
    {
        DataGenerator::cleanUp();
    }

    /**
     * Test method _relinkOrDeleteUsers
     */
    public function test_relinkOrDeleteUsers()
    {
        // Insert an agency
        $doAgency = OA_Dal::factoryDO('agency');
        $agencyId = DataGenerator::generateOne($doAgency);
        $managerAccountId = DataGenerator::getReferenceId('accounts');

        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->get($agencyId);
        $managerAccountId = $doAgency->account_id;

        // Create admin account
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);

        // Create user linked to admin account
        // Default account for this user is set to manager account
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->username = 'admin';
        $adminUserID = DataGenerator::generateOne($doUsers);

        $doAccountsUserAssoc = OA_Dal::factoryDO('account_user_assoc');
        $doAccountsUserAssoc->account_id = $adminAccountId;
        $doAccountsUserAssoc->user_id = $adminUserID;
        DataGenerator::generateOne($doAccountsUserAssoc);

        // Create manager user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->username = 'manager';
        $managerUserID = DataGenerator::generateOne($doUsers);

        // Now delete Agency
        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->agencyid = $agencyId;
        $doAgency->onDeleteCascade = false; // Disable cascade delete
        $doAgency->delete();

        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->get($managerAccountId);
        // Relink / Delete users here
        $doAccounts->_relinkOrDeleteUsers();

        // Test: admin user exists, linked to admin account
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->user_id = $adminUserID;
        $doUsers->find();
        $this->assertTrue($doUsers->fetch());
        $this->assertEqual($doUsers->default_account_id, $adminAccountId);

        // Test: manager users is deleted
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->user_id = $managerUserID;
        $doUsers->find();
        $this->assertFalse($doUsers->fetch());
    }
}
