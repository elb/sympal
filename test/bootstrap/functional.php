<?php

if (!isset($app))
{
  $app = 'sympal';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

function sfSympalPlugin_cleanup()
{
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/cache');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/log');
}
sfSympalPlugin_cleanup();

copy(dirname(__FILE__).'/../fixtures/project/data/fresh_test_db.sqlite', dirname(__FILE__).'/../fixtures/project/data/test.sqlite');
register_shutdown_function('sfSympalPlugin_cleanup');

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
$context = sfContext::createInstance($configuration);
new sfDatabaseManager($configuration);

// so that all notices will appear
error_reporting(E_ALL);

// bring in a few of the unit-test files
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';
require_once sfConfig::get('sf_lib_dir').'/test/unitHelper.php';