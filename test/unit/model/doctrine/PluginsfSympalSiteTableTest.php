<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(12);
$tbl = Doctrine_Core::getTable('sfSympalSite');

$t->info('1 - Test fetchCurrent().');
  $site = $tbl->fetchCurrent();
  $t->is($site, null, '->fetchCurrent() does not automatically create the record for you.');

  $site = $tbl->fetchCurrent(true);
  $t->is(get_class($site), 'sfSympalSite', '->fetchCurrent(true) will create the record');
  $t->is($site->name, 'sympal', 'The new site record is given the right name.');
  $t->is($site->slug, 'sympal', 'The new site record is given the right slug.');

  $site = $tbl->fetchCurrent(true);
  $t->is(get_class($site), 'sfSympalSite', '->fetchCurrent() now returns the existent record');
  $siteCount = $tbl->createQuery()->count();
  $t->is($siteCount, 1, 'Only 1 site record was created throughout the process.');

$t->info('1 - Test fetchSite().');
  Doctrine_Query::create()->from('sfSympalSite')->delete()->execute();
  $site = $tbl->fetchSite('other');
  $t->is($site, null, '->fetchSite() does not automatically create the record for you.');

  $site = $tbl->fetchSite('other', true);
  $t->is(get_class($site), 'sfSympalSite', '->fetchSite(true) will create the record');
  $t->is($site->name, 'other', 'The new site record is given the right name.');
  $t->is($site->slug, 'other', 'The new site record is given the right slug.');

  $site = $tbl->fetchSite('other');
  $t->is(get_class($site), 'sfSympalSite', '->fetchSite() now returns the existent record');
  $siteCount = $tbl->createQuery()->count();
  $t->is($siteCount, 1, 'Only 1 site record was created throughout the process.');