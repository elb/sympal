<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePage extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('page');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => '4'));
        $this->hasColumn('entity_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('title', 'string', 255, array('type' => 'string', 'notnull' => true, 'length' => '255'));
        $this->hasColumn('disable_comments', 'boolean', null, array('type' => 'boolean'));
    }

    public function setUp()
    {
        $sfsympalentitytype0 = new sfSympalEntityType();
        $this->actAs($sfsympalentitytype0);
    }
}