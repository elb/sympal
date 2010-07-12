<?php

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';
require_once sfConfig::get('sf_lib_dir').'/test/unitHelper.php';

$t = new lime_test();

$product = new Product();
$tbl = Doctrine_Core::getTable('Product');

$t->info('1 - Check for the correct listeners');

  has_listener($t, $product, 'sfSympalContentTypeListener');

$t->info('2 - Test getBaseContentQueryTableProxy()');

  $product->save();
  $product = $tbl->getBaseContentQuery()->fetchOne();

  $t->info('  2.1 - $product->Content should require no extra queries');
  $profiler = create_doctrine_profiler();
  $content = $product->Content;
  $t->is(count_queries($profiler), 0, 'Make sure no extra query is made to get the Product');

  $profiler = create_doctrine_profiler();
  $content->CreatedBy;
  $t->is(count_queries($profiler), 0, '$product->Content->CreatedBy causes no extra queries.');

  $product->Translation->name;
  $t->is(count_queries($profiler), 0, '$product->Translation->* causes no extra queries.');

  $content->Type;
  $t->is(count_queries($profiler), 0, '$product->Content->Type causes no extra queries.');
  $content->Site;
  $t->is(count_queries($profiler), 0, '$product->Content->Site causes no extra queries.');
  $content->Translation->page_title;
  $t->is(count_queries($profiler), 0, '$product->Content->Translation->* causes no extra queries.');

  $product->Photos;
  $t->is(count_queries($profiler), 0, '$product->Photos causes no extra queries due to the appendBaseContentQuery() hook.');

  $t->info('  2.2 - Test the sympal.content.get_base_content_query event');

    // makes the appendBaseContentQuery() get skipped
    sfConfig::set('skip_base_content_query_join', true);
    function listen_to_base_query(sfEvent $event)
    {
      $event['query']->leftJoin('cr.Photos crp');
    }
    $configuration->getEventDispatcher()->connect('sympal.content.get_base_content_query', 'listen_to_base_query');
    // test the even that's thrown at the end of the base query.

    $tbl->clear();
    $product = $tbl->getBaseContentQuery()->fetchOne();
    $profiler = create_doctrine_profiler();
    $product->Photos;
    $t->is(count_queries($profiler), 0, '->Photos causes no extra queries because we joined it in via an event.');