<?php

/**
  * Tests that configured getters and setters are passed from the content
  * type model to its related sfSympalContent record.
  */

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(4);

$t->info('...create a Product record.');
$product = new Product();
$product->name = 'test product';
$product->Content->page_title = 'test page title';
$product->save();

$t->info('1 - Test that the getter passes to the content type record (and that the filter_fields option is respected).');
  $t->is($product->page_title, 'test page title', 'The ->page_title field is passed to the Content record.');

$t->info('2 - Test that the setter passes to the Content record.');
  $product->slug = 'brand-new-slug';
  $product->save();

  Doctrine_Core::getTable('sfSympalContent')->getConnection()->clear();
  $content = Doctrine_Query::create()
    ->from('sfSympalContent')
    ->fetchOne();

  $t->is($content->slug, 'brand-new-slug', 'The setter was also passed properly to the Content.');

$t->info('3 - Test that the proper exceptions are thrown for all other fields.');
  try
  {
    // reference a fake field
    echo $product->fake_field;
    $t->fail('Exception not thrown');
  }
  catch (Doctrine_Record_UnknownPropertyException $e)
  {
    $t->pass('Exception thrown.');
  }

  try
  {
    // setting a fake field
    $product->fake_field = 'setting a fake field';
    $t->fail('Exception not thrown');
  }
  catch (Doctrine_Record_UnknownPropertyException $e)
  {
    $t->pass('Exception thrown.');
  }