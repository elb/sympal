<?php

/**
  * Tests that getters and setters are passed from sfSympalContent
  * to its content type record
  */

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(4);

$t->info('...create a content type for Product');
$type = new sfSympalContentType();
$type->name = 'Product';
$type->label = 'Product';
$type->save();

$t->info('...create a Content record linking to Product');
$content = sfSympalContent::createNew('product');
$content->Product->name = 'test product';
$content->save();

$t->info('1 - Test that the getter passes to the content type record.');
  $t->is($content->name, 'test product', 'The ->title field is passed to the content type record.');

$t->info('2 - Test that the setter passes to the content type record.');
  $content->price = 19.99;
  $content->save();

  Doctrine_Core::getTable('Product')->getConnection()->clear();
  $product = Doctrine_Query::create()
    ->from('Product')
    ->fetchOne();

  $t->is($product->price, 19.99, 'The setter was also passed properly to the content type record.');

$t->info('3 - Test that the proper exceptions are thrown for all other fields.');
  try
  {
    // reference a fake field
    echo $content->fake_field;
    $t->fail('Exception not thrown');
  }
  catch (Doctrine_Record_UnknownPropertyException $e)
  {
    $t->pass('Exception thrown.');
  }

  try
  {
    // setting a fake field
    $content->fake_field = 'setting a fake field';
    $t->fail('Exception not thrown');
  }
  catch (Doctrine_Record_UnknownPropertyException $e)
  {
    $t->pass('Exception thrown.');
  }