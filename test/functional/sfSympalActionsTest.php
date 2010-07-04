<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$browser->info('1 - Test the clear cache method');
  $cacheFile = sfConfig::get('sf_app_cache_dir').'/touched.cache';
  file_put_contents($cacheFile, 'file');
  $browser->test()->is(file_exists($cacheFile), true, 'Our cache file exists to begin with.');
  $browser->get('/actions/clear-cache');
  $browser->test()->is(file_exists($cacheFile), false, 'Our cache file no longer exists.');

$browser->info('2 - Test the getSympalConfiguration() method.')
  ->get('/actions/get-sympal-configuration')

  ->with('response')->begin()
    ->isStatusCode(200)
    ->matches('/sfSympalPluginConfiguration/')
  ->end()
;