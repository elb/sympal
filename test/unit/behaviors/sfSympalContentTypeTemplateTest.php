<?php

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';
require_once sfConfig::get('sf_lib_dir').'/test/unitHelper.php';

$t = new lime_test(4);

$product = new Product();

$t->info('1 - Test that the template initializes the filter to Content.');

  $product->Content->page_title = 'unit test';
  $t->is($product->page_title, 'unit test', 'The sfSympalContentTypeFilter is applied');

$t->info('2 - Check for the correct templates and filters');

  has_filter($t, $product, 'sfSympalContentTypeFilter');
  has_listener($t, $product, 'sfSympalContentTypeListener');
  has_listener($t, $product, 'Doctrine_Template_Listener_Sluggable');