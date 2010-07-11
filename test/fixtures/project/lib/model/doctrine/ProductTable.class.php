<?php


class ProductTable extends Doctrine_Table
{
  // hook into the getFullTypeQuery query to join to the Photos
  public function appendBaseContentQuery(Doctrine_Query $q)
  {
    // just allows us to control this from the test
    if (!sfConfig::get('skip_base_content_query_join'))
    {
      $q->leftJoin('cr.Photos crp');
    }

    return $q;
  }
}