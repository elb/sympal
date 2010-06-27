<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins(array(
      'sfDoctrinePlugin',
      'sfDoctrineGuardPlugin',
      'sfSympalPlugin',
    ));

    $this->setPluginPath('sfSympalPlugin', dirname(__FILE__).'/../../../../');
  }

  public function configureDoctrineConnection(Doctrine_Connection $conn)
  {
    $conn->setCollate('utf8_unicode_ci');
  }
}
