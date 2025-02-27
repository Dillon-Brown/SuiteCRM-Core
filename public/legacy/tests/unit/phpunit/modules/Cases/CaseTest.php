<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class aCaseTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testaCase()
    {
        // Execute the constructor and check for the Object type and  attributes
        $aCase = BeanFactory::newBean('Cases');
        $this->assertInstanceOf('aCase', $aCase);
        $this->assertInstanceOf('Basic', $aCase);
        $this->assertInstanceOf('SugarBean', $aCase);

        $this->assertAttributeEquals('Cases', 'module_dir', $aCase);
        $this->assertAttributeEquals('Case', 'object_name', $aCase);
        $this->assertAttributeEquals('cases', 'table_name', $aCase);
        $this->assertAttributeEquals('accounts_cases', 'rel_account_table', $aCase);
        $this->assertAttributeEquals('contacts_cases', 'rel_contact_table', $aCase);
        $this->assertAttributeEquals(true, 'importable', $aCase);
        $this->assertAttributeEquals(true, 'new_schema', $aCase);
    }

    public function testget_summary_text()
    {
        $aCase = BeanFactory::newBean('Cases');
        $this->assertEquals(null, $aCase->get_summary_text());

        $aCase->name = 'test';
        $this->assertEquals('test', $aCase->get_summary_text());
    }

    public function testlistviewACLHelper()
    {
        self::markTestIncomplete('environment dependency');


        $aCase = BeanFactory::newBean('Cases');
        $expected = array('MAIN' => 'span', 'ACCOUNT' => 'span');
        $actual = $aCase->listviewACLHelper();
        $this->assertSame($expected, $actual);
    }

    public function testsave_relationship_changes()
    {
        $aCase = BeanFactory::newBean('Cases');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $aCase->save_relationship_changes(true);
            $aCase->save_relationship_changes(false);

            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testset_case_contact_relationship()
    {
        $aCase = BeanFactory::newBean('Cases');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $aCase->set_case_contact_relationship(1);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testfill_in_additional_list_fields()
    {
        $aCase = BeanFactory::newBean('Cases');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $aCase->fill_in_additional_list_fields();
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testfill_in_additional_detail_fields()
    {
        $aCase = BeanFactory::newBean('Cases');
        $aCase->assigned_user_id = 1;
        $aCase->created_by = 1;
        $aCase->modified_user_id = 1;

        $aCase->fill_in_additional_detail_fields();

        $this->assertEquals('Administrator', $aCase->assigned_user_name);
        $this->assertEquals('Administrator', $aCase->created_by_name);
        $this->assertEquals('Administrator', $aCase->modified_by_name);
    }

    public function testget_contacts()
    {
        $aCase = BeanFactory::newBean('Cases');
        $result = $aCase->get_contacts();
        $this->assertFalse(is_array($result));
        $this->assertEquals(false, $result);
    }

    public function testget_list_view_data()
    {
        $aCase = BeanFactory::newBean('Cases');
        $current_theme = SugarThemeRegistry::current();
        //test without setting attributes
        $expected = array(
                'DELETED' => 0,
                'STATE' => 'Open',
                'UPDATE_TEXT' => '',
                'NAME' => '<em>blank</em>',
                'PRIORITY' => '',
                'STATUS' => '',
                'ENCODED_NAME' => null,
                'CASE_NUMBER' => null,
                'SET_COMPLETE' => '~'
                                .preg_quote('<a href=\'index.php?return_module=Home&return_action=index&action=EditView&module=Cases&record=&status=Closed\'><img src="themes/'.$current_theme.'/images/close_inline.png?v=')
                                .'[\w-]+'
                                .preg_quote('"    title=Close border=\'0\' alt="Close" /></a>')
                                .'~',
        );

        $actual = $aCase->get_list_view_data();
        //$this->assertSame($expected ,$actual);
        $this->assertEquals($expected['STATE'], $actual['STATE']);
        $this->assertEquals($expected['UPDATE_TEXT'], $actual['UPDATE_TEXT']);
        $this->assertEquals($expected['NAME'], $actual['NAME']);
        $this->assertEquals($expected['PRIORITY'], $actual['PRIORITY']);
        $this->assertRegExp($expected['SET_COMPLETE'], $actual['SET_COMPLETE']);

        //test with attributes preset
        $aCase->name = 'test';
        $aCase->priority = 'P1';
        $aCase->status = 'Open_New';
        $aCase->case_number = 1;

        $expected = array(
                'NAME' => 'test',
                'DELETED' => 0,
                'CASE_NUMBER' => 1,
                'STATUS' => 'New',
                'PRIORITY' => 'High',
                'STATE' => 'Open',
                'UPDATE_TEXT' => '',
                'ENCODED_NAME' => 'test',
                'SET_COMPLETE' => '<a href=\'index.php?return_module=Home&return_action=index&action=EditView&module=Cases&record=&status=Closed\'><img src="themes/'.$current_theme.'/images/close_inline.png?v=fqXdFZ_r6FC1K7P_Fy3mVw"    title=Close border=\'0\' alt="Close" /></a>',
        );

        $actual = $aCase->get_list_view_data();
        //$this->assertSame($expected ,$actual);
        $this->assertEquals($expected['NAME'], $actual['NAME']);
        $this->assertEquals($expected['CASE_NUMBER'], $actual['CASE_NUMBER']);
        $this->assertEquals($expected['STATUS'], $actual['STATUS']);
        $this->assertEquals($expected['PRIORITY'], $actual['PRIORITY']);
        $this->assertEquals($expected['STATE'], $actual['STATE']);
    }

    public function testbuild_generic_where_clause()
    {
        $aCase = BeanFactory::newBean('Cases');

        //test with string
        $expected = "(cases.name like 'test%' or accounts.name like 'test%')";
        $actual = $aCase->build_generic_where_clause('test');
        $this->assertSame($expected, $actual);

        //test with number
        $expected = "(cases.name like '1%' or accounts.name like '1%' or cases.case_number like '1%')";
        $actual = $aCase->build_generic_where_clause(1);
        $this->assertSame($expected, $actual);
    }

    public function testset_notification_body()
    {
        $aCase = BeanFactory::newBean('Cases');

        $aCase->name = 'test';
        $aCase->priority = 'P1';
        $aCase->status = 'Open_New';
        $aCase->description = 'some text';

        $result = $aCase->set_notification_body(new Sugar_Smarty(), $aCase);

        $this->assertEquals($aCase->name, $result->_tpl_vars['CASE_SUBJECT']);
        $this->assertEquals('High', $result->_tpl_vars['CASE_PRIORITY']);
        $this->assertEquals('New', $result->_tpl_vars['CASE_STATUS']);
        $this->assertEquals($aCase->description, $result->_tpl_vars['CASE_DESCRIPTION']);
    }

    public function testbean_implements()
    {
        $aCase = BeanFactory::newBean('Cases');
        $this->assertEquals(false, $aCase->bean_implements('')); //test with blank value
        $this->assertEquals(false, $aCase->bean_implements('test')); //test with invalid value
        $this->assertEquals(true, $aCase->bean_implements('ACL')); //test with valid value
    }

    public function testsave()
    {
        $aCase = BeanFactory::newBean('Cases');
        $aCase->name = 'test';
        $aCase->priority = 'P1';

        $aCase->save();

        //test for record ID to verify that record is saved
        $this->assertTrue(isset($aCase->id));
        $this->assertEquals(36, strlen($aCase->id));

        //mark the record as deleted and verify that this record cannot be retrieved anymore.
        $aCase->mark_deleted($aCase->id);
        $result = $aCase->retrieve($aCase->id);
        $this->assertEquals(null, $result);
    }

    public function testgetEmailSubjectMacro()
    {
        $aCase = BeanFactory::newBean('Cases');
        $result = $aCase->getEmailSubjectMacro();
        $this->assertEquals('[CASE:%1]', $result);
    }

    public function testgetAccount()
    {
        $aCase = BeanFactory::newBean('Cases');
        $result = $aCase->getAccount(1);
        $this->assertTrue(is_array($result));
        $this->assertEquals(array('account_name' => '', 'account_id' => ''), $result);
    }
}
