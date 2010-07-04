<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(3);
$tbl = Doctrine_Core::getTable('sfSympalSite');
create_content_type($t, 'Product');
$site = $tbl->fetchCurrent(true); // create the site record

$t->info('1 - Test that the delete recurses on the application level to the sfSympalContent records and then to the content type records');

  // create a content record
  $product = sfSympalContent::createNew('Product');
  $product->save();

  $site->refreshRelated('Content');
  $t->isnt(count($site->Content), 1, 'Sanity check: the sfSympalSite record has one Content record');

  $site->delete();

  $site->refreshRelated('Content');
  $products = Doctrine_Core::getTable('Product')->createQuery()->execute();
  $t->is(count($site->Content), 0, 'The site now has no content. This would happen with or without the application-level delete recursion');
  $t->is(count($products), 0, 'All of the Product records are gone due to the application-level delete recursion onto sfSympalContent');
