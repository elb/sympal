<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(6);
$tbl = Doctrine_Core::getTable('sfSympalContent');

$t->info('1 - Test the ::createNew() method');

  $t->info('  1.1 - Passing in an invalid type throws an exception');
    test_create_new_bad_type($t, 'fake');
    test_create_new_bad_type($t, new stdClass());
    test_create_new_bad_type($t, array());


function test_create_new_bad_type(lime_test $t, $type)
{
  try
  {
    sfSympalContent::createNew($type);
    $t->fail('Exception now thrown');
  }
  catch (InvalidArgumentException $e)
  {
    $t->pass('Exception thrown ' . $e->getMessage());
  }
}