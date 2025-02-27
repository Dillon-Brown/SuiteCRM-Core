<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class AOS_InvoicesTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testAOS_Invoices()
    {
        // Execute the constructor and check for the Object type and  attributes
        $aosInvoices = BeanFactory::newBean('AOS_Invoices');
        $this->assertInstanceOf('AOS_Invoices', $aosInvoices);
        $this->assertInstanceOf('Basic', $aosInvoices);
        $this->assertInstanceOf('SugarBean', $aosInvoices);

        $this->assertAttributeEquals('AOS_Invoices', 'module_dir', $aosInvoices);
        $this->assertAttributeEquals('AOS_Invoices', 'object_name', $aosInvoices);
        $this->assertAttributeEquals('aos_invoices', 'table_name', $aosInvoices);
        $this->assertAttributeEquals(true, 'new_schema', $aosInvoices);
        $this->assertAttributeEquals(true, 'disable_row_level_security', $aosInvoices);
        $this->assertAttributeEquals(true, 'importable', $aosInvoices);
    }

    public function testSaveAndMark_deleted()
    {
        $aosInvoices = BeanFactory::newBean('AOS_Invoices');
        $aosInvoices->name = 'test';

        $aosInvoices->save();

        //test for record ID to verify that record is saved
        $this->assertTrue(isset($aosInvoices->id));
        $this->assertEquals(36, strlen($aosInvoices->id));
        $this->assertGreaterThan(0, $aosInvoices->number);

        //mark the record as deleted and verify that this record cannot be retrieved anymore.
        $aosInvoices->mark_deleted($aosInvoices->id);
        $result = $aosInvoices->retrieve($aosInvoices->id);
        $this->assertEquals(null, $result);
    }
}
