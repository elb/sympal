<?php

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';
require_once sfConfig::get('sf_lib_dir').'/test/unitHelper.php';

$t = new lime_test(4);

create_content_type($t, 'Product');
$content = create_content($t, 'Product');

$t->info('1 - Test that the template initializes the filter.');

  $content->Product->name = 'unit test';
  $t->is($content->name, 'unit test', 'The sfSympalContentFilter is applied');

$t->info('2 - Test that methods are passed to the content type');

  $t->is($content->testMethod(), 'testing', 'The unknown method is passed to the content type model');
  $t->is($content->testMethod('ret'), 'ret', 'The unknown method is passed to the content type model with args');

  $t->info('  2.1 - Test that calling an undefined method still throws an exception.');
  try
  {
    $content->totalBsMethod();
    $t->fail('Exception now thrown');
  }
  catch (Exception $e)
  {
    $t->pass('Exception thrown' . $e->getMessage());
  }
