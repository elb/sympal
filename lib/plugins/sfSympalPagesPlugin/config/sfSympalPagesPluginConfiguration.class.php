<?php

class sfSympalPagesPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    sfConfig::set('sf_enabled_modules', array_merge(
      sfConfig::get('sf_enabled_modules', array()),
      array('sympal_page')
    ));
  }
}