<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginPageTable extends Doctrine_Table
{
  public function getEntityQuery()
  {
    $q = Doctrine::getTable('Entity')->getBaseQuery()
      ->innerJoin('e.Page p');

    return $q;
  }
}