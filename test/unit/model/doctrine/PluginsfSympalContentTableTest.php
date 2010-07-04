<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(5);
$tbl = Doctrine_Core::getTable('sfSympalContent');

create_content_type($t, 'Product');
$content = create_content($t, 'Product');
$user = create_guard_user('admin');
$content->CreatedBy = $user;
$content->save();
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
  
  $content->Translation->name;
  $t->is(count_queries($profiler), 0, '->Translation->* causes no extra queries.');

  $content->Photos;
  $t->is(count_queries($profiler), 0, '->Photos causes no extra queries due to the getContentQuery() hook.');

  