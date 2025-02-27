<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class AOS_ContractsTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testAOS_Contracts()
    {
        // Execute the constructor and check for the Object type and attributes
        $aosContracts = BeanFactory::newBean('AOS_Contracts');
        $this->assertInstanceOf('AOS_Contracts', $aosContracts);
        $this->assertInstanceOf('Basic', $aosContracts);
        $this->assertInstanceOf('SugarBean', $aosContracts);

        $this->assertAttributeEquals('AOS_Contracts', 'module_dir', $aosContracts);
        $this->assertAttributeEquals('AOS_Contracts', 'object_name', $aosContracts);
        $this->assertAttributeEquals('aos_contracts', 'table_name', $aosContracts);
        $this->assertAttributeEquals(true, 'new_schema', $aosContracts);
        $this->assertAttributeEquals(true, 'disable_row_level_security', $aosContracts);
        $this->assertAttributeEquals(true, 'importable', $aosContracts);

        $this->assertTrue(isset($aosContracts->renewal_reminder_date));
    }

    public function testsaveAndDelete()
    {
        $aosContracts = BeanFactory::newBean('AOS_Contracts');
        $aosContracts->name = 'test';

        $aosContracts->save();

        //test for record ID to verify that record is saved
        $this->assertTrue(isset($aosContracts->id));
        $this->assertEquals(36, strlen($aosContracts->id));

        //mark the record as deleted and verify that this record cannot be retrieved anymore.
        $aosContracts->mark_deleted($aosContracts->id);
        $result = $aosContracts->retrieve($aosContracts->id);
        $this->assertEquals(null, $result);
    }

    public function testCreateReminderAndCreateLinkAndDeleteCall()
    {
        $call = new call();

        $aosContracts = BeanFactory::newBean('AOS_Contracts');
        $aosContracts->name = 'test';

        //test createReminder()
        $aosContracts->createReminder();

        //verify record ID to check that record is saved
        $this->assertTrue(isset($aosContracts->call_id));
        $this->assertEquals(36, strlen($aosContracts->call_id));

        //verify the parent_type value set by createReminder()
        $call->retrieve($aosContracts->call_id);
        $this->assertAttributeEquals('AOS_Contracts', 'parent_type', $call);

        //test createLink() and verify the parent_type value
        $aosContracts->createLink();
        $call->retrieve($aosContracts->call_id);
        $this->assertAttributeEquals('Accounts', 'parent_type', $call);

        //delete the call and verify that this record cannot be retrieved anymore.
        $aosContracts->deleteCall();
        $result = $call->retrieve($aosContracts->call_id);
        $this->assertEquals(null, $result);
    }
}
