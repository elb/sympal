<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(11);
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

$t->info('2 - Run a similar test on the Page model');
  $t->info('  2.1 - This tests that even a very simple content type object gets the ->Content and ->Content->ContentType objects');

  $page = new Page();
  $page->save();
  $page->refresh(true);

  $t->is(get_class($page), 'Page', 'The page persisted as normal');
  $t->isnt($page->Content->id, null, 'The ->Content relationship was saved correctly.');
  $t->isnt($page->Content->Type->id, null, 'The $content->Type relationship was saved correctly.');
  $t->is($page->Content->Type->default_path, '/page/:id', '$type->default_path was saved as /page/:id because no slug field');
  $t->is($page->Content->Type->type_key, 'page', '$type->type_key was saved correctly.');
