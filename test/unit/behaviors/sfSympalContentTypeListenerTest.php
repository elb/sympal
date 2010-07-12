<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(4);
$tbl = Doctrine_Core::getTable('Product');

$t->info('...create a Product record.');
$product = new Product();
$product->name = 'test product';
$product->Content->page_title = 'test page title';
$product->save();
$tbl->clear();

$t->info('1 - Test the ->Content and ->Content->ContentType relations on the new Product');

  $product = $tbl->createQuery()->fetchOne();
  $content = Doctrine_Query::create()->from('sfSympalContent')->fetchOne();
  $type = Doctrine_Query::create()->from('sfSympalContentType')->fetchOne();

  $t->is(get_class($product), 'Product', 'The product persisted as normal');
  $t->is($product->content_id, $content->id, 'The ->Content relationship was saved correctly.');
  $t->is($content->page_title, 'test page title', '$content->page_title persisted normally');
  $t->is($content->content_type_id, $type->id, 'The $content->Type relationship was saved correctly.');
  $t->is($type->default_path, '/product/:slug', '$type->default_path was saved as /product/:slug because of the slug field');
  $t->is($type->type_key, 'product', '$type->type_key was saved correctly.');
  