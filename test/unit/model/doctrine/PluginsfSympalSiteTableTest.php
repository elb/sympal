<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(5);
$tbl = Doctrine_Core::getTable('sfSympalSite');

$t->info('1 - Test fetchCurrent().');
  $site = $tbl->fetchCurrent();
  $t->is($site, null, '->fetchCurrent() does not automatically create the record for you.');

  $site = $tbl->fetchCurrent(true);
  $t->is(get_class($site), 'sfSympalSite', '->fetchCurrnet(true) will create the record');
  $t->is($site->name, 'sympal', 'The new site record is given the right name.');
  $t->is($site->slug, 'sympal', 'The new site record is given the right slug.');

  $site = $tbl->fetchCurrent(true);
  $t->is(get_class($site), 'sfSympalSite', '->fetchCurrnet() now returns the existent record');
  $siteCount = $tbl->createQuery()->count();
  $t->is($siteCount, 1, 'Only 1 site record was created throughout the process.');