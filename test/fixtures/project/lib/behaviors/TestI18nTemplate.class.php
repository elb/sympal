<?php
// dummy template which does nothing but apply the special i18n filter
class TestI18nTemplate extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalDoctrineRecordI18nFilter());
  }

}