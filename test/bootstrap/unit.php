<?php

$projectPath = dirname(__FILE__).'/../fixtures/project';
require_once($projectPath.'/config/ProjectConfiguration.class.php');

require_once(dirname(__FILE__).'/cleanup.php');

if (!isset($app))
{
  $configuration = new ProjectConfiguration($projectPath);
}
else
{
  $configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
  $context = sfContext::createInstance($configuration);
}

if (isset($database) && $database)
{
  $database = new sfDatabaseManager($configuration);
}

require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

if (!isset($context))
{
  require_once dirname(__FILE__).'/../../config/sfSympalPluginConfiguration.class.php';
  $plugin_configuration = new sfSympalPluginConfiguration($configuration, dirname(__FILE__).'/../..');
}
else
{
  $plugin_configuration = $context->getConfiguration()->getPluginConfiguration('sfSympalPlugin');
}