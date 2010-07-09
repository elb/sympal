<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(10);
$tbl = Doctrine_Core::getTable('sfSympalContent');

$product = new Product();
$user = Doctrine_Core::getTable('sfGuardUser')->findOneByUsername('admin');
$product->Content->CreatedBy = $user;
$product->save();
$tbl->clear();

$t->info('1 - Test getTypeQuery() and getFullTypeQuery()');
  $content = $tbl->getTypeQuery('Product')->fetchOne();

  $t->info('  1.1 - $content->Product should require no extra queries');
  $profiler = create_doctrine_profiler();
  $content->Product;
  $t->is(count_queries($profiler), 0, 'Make sure no extra query is made to get the Product');

  $t->info('  1.2 - Test the getFullTypeQuery()');
  $tbl->clear();
  $content = $tbl->getFullTypeQuery('Product')->fetchOne();

  $t->info('    a) The base query should have been used');
  $profiler = create_doctrine_profiler();
  $content->CreatedBy;
  $t->is(count_queries($profiler), 0, 'Due to the base query, ->CreatedBy causes no extra queries.');
  
  $content->Product->Translation->name;
  $t->is(count_queries($profiler), 0, '->Translation->* causes no extra queries.');

  $content->Photos;
  $t->is(count_queries($profiler), 0, '->Photos causes no extra queries due to the getContentQuery() hook.');

$t->info('2 - Test getBaseQuery()');
  $tbl->clear();
  $content = $tbl->getBaseQuery()->fetchOne();
  $profiler = create_doctrine_profiler();

  $content->Type;
  $t->is(count_queries($profiler), 0, '->Type causes no extra queries.');
  $content->Site;
  $t->is(count_queries($profiler), 0, '->Site causes no extra queries.');
  $content->Translation->page_title;
  $t->is(count_queries($profiler), 0, '->Translation->* causes no extra queries.');

$t->info('3 - Test the sympal.content.get_base_query event');

  function listen_to_base_query(sfEvent $event)
  {
    $event['query']->leftJoin('c.Product cr');
  }
  $configuration->getEventDispatcher()->connect('sympal.content.get_base_query', 'listen_to_base_query');
  // test the even that's thrown at the end of the base query.

  $tbl->clear();
  $content = $tbl->getBaseQuery()->fetchOne();
  $profiler = create_doctrine_profiler();
  $content->Product;
  $t->is(count_queries($profiler), 0, '->Product causes no extra queries because we joined it in via an event.');

$t->info('4 - Test addPublishedQuery()');
  $tbl->clear();
  $content = $tbl->addPublishedQuery()->fetchOne();
  $t->is($content, null, '->addPublishedQuery() correctly adds the published criteria');

  $content = $tbl->createQuery()->fetchOne();
  $content->date_published = '2010-01-01';
  $content->save();
  $tbl->clear();

  $content = $tbl->addPublishedQuery()->fetchOne();
  $t->is(get_class($content), 'sfSympalContent', '->addPublishedQuery() correctly adds the published criteria');
