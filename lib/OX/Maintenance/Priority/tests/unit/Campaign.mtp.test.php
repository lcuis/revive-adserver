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

require_once MAX_PATH . '/lib/OX/Maintenance/Priority/Campaign.php';

/**
 * A class for testing the OX_Maintenance_Priority_Campaign class.
 *
 * @package    OpenXMaintenance
 * @subpackage TestSuite
 */
class Test_OX_Maintenance_Priority_Campaign extends UnitTestCase
{
    /**
     * The class constructor method.
     */
    public function __construct()
    {
        parent::__construct();
        Mock::generate('MAX_Dal_Entities');
        Mock::generate('OA_Dal_Maintenance_Priority');
        Mock::generatePartial(
            'OX_Maintenance_Priority_Campaign',
            'MockPartialOX_Maintenance_Priority_Campaign',
            ['_abort']
        );
    }

    /**
     * A method to be called before every test to store default
     * mocked data access layers in the service locator.
     */
    public function setUp()
    {
        $oServiceLocator = OA_ServiceLocator::instance();
        $oMaxDalEntities = new MockMAX_Dal_Entities($this);
        $oServiceLocator->register('MAX_Dal_Entities', $oMaxDalEntities);
        $oMaxDalMaintenancePriority = new MockOA_Dal_Maintenance_Priority($this);
        $oServiceLocator->register('OA_Dal_Maintenance_Priority', $oMaxDalMaintenancePriority);
    }

    /**
     * A method to be called after every test to remove the
     * mocked data access layers from the service locator.
     *
     */
    public function tearDown()
    {
        $oServiceLocator = OA_ServiceLocator::instance();
        $oServiceLocator->remove('MAX_Dal_Entities');
        $oServiceLocator->remove('OA_Dal_Maintenance_Priority');
    }

    /**
     * A method to test the OX_Maintenance_Priority_Campaign() method.
     *
     * Requirements:
     * Test 1: Test with invalid input and ensure the _abort() method is called.
     * Test 2: Test with the "old" values, and ensure they are correctly set.
     * Test 3: Test with the "new" values, and ensure they are correctly set.
     */
    public function testOX_Maintenance_Priority_Campaign()
    {
        // Test 1
        $aParams = 'foo';
        $oCampaign = new MockPartialOX_Maintenance_Priority_Campaign($this);
        $oCampaign->expectCallCount('_abort', 1);
        (new ReflectionMethod(OX_Maintenance_Priority_Campaign::class, '__construct'))->invoke($oCampaign, $aParams);
        $oCampaign->tally();

        $aParams = [];
        $oCampaign = new MockPartialOX_Maintenance_Priority_Campaign($this);
        $oCampaign->expectCallCount('_abort', 1);
        (new ReflectionMethod(OX_Maintenance_Priority_Campaign::class, '__construct'))->invoke($oCampaign, $aParams);
        $oCampaign->tally();

        $aParams = ['campaign_id' => 'foo'];
        $oCampaign = new MockPartialOX_Maintenance_Priority_Campaign($this);
        $oCampaign->expectCallCount('_abort', 1);
        (new ReflectionMethod(OX_Maintenance_Priority_Campaign::class, '__construct'))->invoke($oCampaign, $aParams);
        $oCampaign->tally();

        $aParams = ['priority' => 5];
        $oCampaign = new MockPartialOX_Maintenance_Priority_Campaign($this);
        $oCampaign->expectCallCount('_abort', 1);
        (new ReflectionMethod(OX_Maintenance_Priority_Campaign::class, '__construct'))->invoke($oCampaign, $aParams);
        $oCampaign->tally();

        // Test 2
        $aParams = [
            'campaignid' => 1,
            'activate_time' => '2005-01-01',
            'expire_time' => '2005-01-31',
            'views' => 1000000,
            'clicks' => 100000,
            'conversions' => 1000,
            'target_impression' => 2,
            'target_click' => 3,
            'target_conversion' => 4,
            'priority' => 5
        ];
        $oCampaign = new OX_Maintenance_Priority_Campaign($aParams);
        $this->assertEqual($oCampaign->id, 1);
        $this->assertEqual($oCampaign->activateTime, '2005-01-01');
        $this->assertEqual($oCampaign->expireTime, '2005-01-31');
        $this->assertEqual($oCampaign->impressionTargetTotal, 1000000);
        $this->assertEqual($oCampaign->clickTargetTotal, 100000);
        $this->assertEqual($oCampaign->conversionTargetTotal, 1000);
        $this->assertEqual($oCampaign->impressionTargetDaily, 2);
        $this->assertEqual($oCampaign->clickTargetDaily, 3);
        $this->assertEqual($oCampaign->conversionTargetDaily, 4);
        $this->assertEqual($oCampaign->priority, 5);

        // Test 3
        $aParams = [
            'campaignid' => 1,
            'activate_time' => '2005-01-01',
            'expire_time' => '2005-01-31',
            'impression_target_total' => 1000000,
            'click_target_total' => 100000,
            'conversion_target_total' => 1000,
            'impression_target_daily' => 2,
            'click_target_daily' => 3,
            'conversion_target_daily' => 4,
            'priority' => 5
        ];
        $oCampaign = new OX_Maintenance_Priority_Campaign($aParams);
        $this->assertEqual($oCampaign->id, 1);
        $this->assertEqual($oCampaign->activateTime, '2005-01-01');
        $this->assertEqual($oCampaign->expireTime, '2005-01-31');
        $this->assertEqual($oCampaign->impressionTargetTotal, 1000000);
        $this->assertEqual($oCampaign->clickTargetTotal, 100000);
        $this->assertEqual($oCampaign->conversionTargetTotal, 1000);
        $this->assertEqual($oCampaign->impressionTargetDaily, 2);
        $this->assertEqual($oCampaign->clickTargetDaily, 3);
        $this->assertEqual($oCampaign->conversionTargetDaily, 4);
        $this->assertEqual($oCampaign->priority, 5);
    }

    /**
     * A method to test the setAdverts() method.
     *
     * Requirements:
     * Test 1: Test with error getting the ads from the database, and
     *         ensure the aAds array remains empty.
     * Test 2: Test with no children ads in the database, and ensure
     *         the aAds array remains empty.
     * Test 3: Test with children ads in the database, and ensure that
     *         the correct entities are created for these ads in the
     *         aAds array.
     */
    public function testSetAdverts()
    {
        $oError = new PEAR_Error();
        $aAds = [
            1 => ['ad_id' => 1, 'type' => 'sql', 'weight' => 2, 'status' => OA_ENTITY_STATUS_RUNNING, ],
            2 => ['ad_id' => 2, 'type' => 'gif', 'weight' => 1, 'status' => OA_ENTITY_STATUS_RUNNING, ],
            3 => ['ad_id' => 3, 'type' => 'sql', 'weight' => 2, 'status' => OA_ENTITY_STATUS_RUNNING, ],
            5 => ['ad_id' => 5, 'type' => 'gif', 'weight' => 3, 'status' => OA_ENTITY_STATUS_AWAITING, ],
        ];
        $oServiceLocator = OA_ServiceLocator::instance();
        $oMaxDalEntities = &$oServiceLocator->get('MAX_Dal_Entities');
        $oMaxDalEntities->setReturnValueAt(0, 'getAdsByCampaignId', $oError);
        $oMaxDalEntities->setReturnValueAt(1, 'getAdsByCampaignId', null);
        $oMaxDalEntities->setReturnValueAt(2, 'getAdsByCampaignId', $aAds);
        $oMaxDalEntities->expectArgumentsAt(0, 'getAdsByCampaignId', [1]);
        $oMaxDalEntities->expectArgumentsAt(1, 'getAdsByCampaignId', [1]);
        $oMaxDalEntities->expectArgumentsAt(2, 'getAdsByCampaignId', [1]);
        $oMaxDalEntities->expectCallCount('getAdsByCampaignId', 3);

        // Test 1
        $aParams = ['campaignid' => 1];
        $oCampaign = new OX_Maintenance_Priority_Campaign($aParams);
        $this->assertTrue(is_array($oCampaign->aAds));
        $this->assertEqual(count($oCampaign->aAds), 0);
        $oCampaign->setAdverts();
        $this->assertTrue(is_array($oCampaign->aAds));
        $this->assertEqual(count($oCampaign->aAds), 0);

        // Test 2
        $aParams = ['campaignid' => 1];
        $oCampaign = new OX_Maintenance_Priority_Campaign($aParams);
        $this->assertTrue(is_array($oCampaign->aAds));
        $this->assertEqual(count($oCampaign->aAds), 0);
        $oCampaign->setAdverts();
        $this->assertTrue(is_array($oCampaign->aAds));
        $this->assertEqual(count($oCampaign->aAds), 0);

        // Test 3
        $this->assertTrue(is_array($oCampaign->aAds));
        $this->assertEqual(count($oCampaign->aAds), 0);
        $oCampaign->setAdverts();
        $this->assertTrue(is_array($oCampaign->aAds));
        $this->assertEqual(count($oCampaign->aAds), 4);
        $this->assertTrue(is_a($oCampaign->aAds[1], 'OA_Maintenance_Priority_Ad'));
        $this->assertTrue(is_a($oCampaign->aAds[2], 'OA_Maintenance_Priority_Ad'));
        $this->assertTrue(is_a($oCampaign->aAds[3], 'OA_Maintenance_Priority_Ad'));
        $this->assertTrue(is_a($oCampaign->aAds[5], 'OA_Maintenance_Priority_Ad'));

        $oMaxDalEntities->tally();
    }

    /**
     * A method to test the setSummaryStatisticsToDate() method.
     *
     * Requirements:
     * Test 1: Test with no delivery to date in the database, and ensure that
     *         zero is set for all delivery values.
     * Test 2: Test with delivery to date in the database, and ensure the values
     *         are correctly stored.
     */
    public function testSetSummaryStatisticsToDate()
    {
        $aCampaignStats = [
            'advertiser_id' => 1,
            'campaign_id' => 1,
            'name' => 'Campaign name',
            'active' => 't',
            'num_children' => 1,
            'sum_requests' => 100,
            'sum_views' => 99,
            'sum_clicks' => 5,
            'sum_conversions' => 1,
        ];
        $oServiceLocator = OA_ServiceLocator::instance();
        $oMaxDalMaintenancePriority = &$oServiceLocator->get('OA_Dal_Maintenance_Priority');
        $oMaxDalMaintenancePriority->setReturnValueAt(0, 'getCampaignStats', null);
        $oMaxDalMaintenancePriority->setReturnValueAt(1, 'getCampaignStats', $aCampaignStats);
        $oMaxDalMaintenancePriority->expectArgumentsAt(0, 'getCampaignStats', [1, false]);
        $oMaxDalMaintenancePriority->expectArgumentsAt(1, 'getCampaignStats', [1, false]);
        $oMaxDalMaintenancePriority->expectCallCount('getCampaignStats', 2);

        // Test 1
        $aParams = ['campaignid' => 1];
        $oCampaign = new OX_Maintenance_Priority_Campaign($aParams);
        $this->assertNull($oCampaign->deliveredRequests);
        $this->assertNull($oCampaign->deliveredImpressions);
        $this->assertNull($oCampaign->deliveredClicks);
        $this->assertNull($oCampaign->deliveredConversions);
        $oCampaign->setSummaryStatisticsToDate();
        $this->assertEqual($oCampaign->deliveredRequests, 0);
        $this->assertEqual($oCampaign->deliveredImpressions, 0);
        $this->assertEqual($oCampaign->deliveredClicks, 0);
        $this->assertEqual($oCampaign->deliveredConversions, 0);

        // Test 2
        $oCampaign->setSummaryStatisticsToDate();
        $this->assertEqual($oCampaign->deliveredRequests, 100);
        $this->assertEqual($oCampaign->deliveredImpressions, 99);
        $this->assertEqual($oCampaign->deliveredClicks, 5);
        $this->assertEqual($oCampaign->deliveredConversions, 1);

        $oMaxDalMaintenancePriority->tally();
    }

    /**
     * A method to test the setSummaryStatisticsToday() method.
     *
     * Requirements:
     * Test 1: Test with no delivery today in the database, and ensure that
     *         zero is set for all delivery values.
     * Test 2: Test with delivery today in the database, and ensure the values
     *         are correctly stored.
     */
    public function testSetSummaryStatisticsToday()
    {
        $aCampaignStats = [
            'advertiser_id' => 1,
            'campaign_id' => 1,
            'name' => 'Campaign Name',
            'active' => 't',
            'num_children' => 1,
            'sum_requests' => 100,
            'sum_views' => 99,
            'sum_clicks' => 5,
            'sum_conversions' => 1,
        ];
        $oServiceLocator = OA_ServiceLocator::instance();
        $oMaxDalMaintenancePriority = &$oServiceLocator->get('OA_Dal_Maintenance_Priority');
        $oMaxDalMaintenancePriority->setReturnValueAt(0, 'getCampaignStats', null);
        $oMaxDalMaintenancePriority->setReturnValueAt(1, 'getCampaignStats', $aCampaignStats);
        $oMaxDalMaintenancePriority->expectArgumentsAt(0, 'getCampaignStats', [1, true, '2006-11-10']);
        $oMaxDalMaintenancePriority->expectArgumentsAt(1, 'getCampaignStats', [1, true, '2006-11-10']);
        $oMaxDalMaintenancePriority->expectCallCount('getCampaignStats', 2);

        // Test 1
        $aParams = ['campaignid' => 1];
        $oCampaign = new OX_Maintenance_Priority_Campaign($aParams);
        $this->assertNull($oCampaign->deliveredRequests);
        $this->assertNull($oCampaign->deliveredImpressions);
        $this->assertNull($oCampaign->deliveredClicks);
        $this->assertNull($oCampaign->deliveredConversions);
        $oCampaign->setSummaryStatisticsToday('2006-11-10');
        $this->assertEqual($oCampaign->deliveredRequests, 0);
        $this->assertEqual($oCampaign->deliveredImpressions, 0);
        $this->assertEqual($oCampaign->deliveredClicks, 0);
        $this->assertEqual($oCampaign->deliveredConversions, 0);

        // Test 2
        $oCampaign->setSummaryStatisticsToday('2006-11-10');
        $this->assertEqual($oCampaign->deliveredRequests, 100);
        $this->assertEqual($oCampaign->deliveredImpressions, 99);
        $this->assertEqual($oCampaign->deliveredClicks, 5);
        $this->assertEqual($oCampaign->deliveredConversions, 1);

        $oMaxDalMaintenancePriority->tally();
    }
}
