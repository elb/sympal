<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(2);
$tbl = Doctrine_Core::getTable('sfSympalContent');

$product = new Product();
$product->save();
$tbl->clear();

$t->info('1 - Test addPublishedQuery()');
  $tbl->clear();
  $content = $tbl->addPublishedQuery()->fetchOne();
  $t->is($content, null, '->addPublishedQuery() correctly adds the published criteria');

  $content = $tbl->createQuery()->fetchOne();
  $content->date_published = '2010-01-01';
  $content->save();
  $tbl->clear();

  $content = $tbl->addPublishedQuery()->fetchOne();
  $t->is(get_class($content), 'sfSympalContent', '->addPublishedQuery() correctly adds the published criteria');
