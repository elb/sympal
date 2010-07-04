<?php


class ProductTable extends Doctrine_Table
{
  // hook into the getFullTypeQuery query to join to the Photos
  public function getContentQuery(Doctrine_Query $q)
  {
    $q->leftJoin('cr.Photos crp');

    return $q;
  }
}