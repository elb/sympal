<?php

/**
 * Class responsible for adding new methods to your sfActions instances
 * 
 * Due to sfSympalExtendClass, this effectively extends sfActions, meaning
 * you can literally call methods like ->getRequest() as you normally would
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalActions extends sfSympalExtendClass
{
  /**
   * Shortcut to the clear cache task from your actions
   *
   * @param array $options 
   * @return void
   */
  public function clearCache(array $options = array())
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->_subject->getContext()->getEventDispatcher(), new sfFormatter());
    $task->run(array(), $options);
  }

  /**
   * Get instance of the sfSympalContentActionLoader for loading and rendering content
   *
   * @return sfSympalContentActionLoader
   */
  public function getSympalContentActionLoader()
  {
    $class = sfSympalConfig::get('content_action_loader_class', null, 'sfSympalContentActionLoader');

    return new $class($this->getSubject());
  }

  /**
   * Shortcut method to get the plugin configuration
   *
   * @return sfSympalPluginConfiguration
   */
  protected function getSympalConfiguration()
  {
    return $this->_subject
      ->getContext()
      ->getConfiguration()
      ->getPluginConfiguration('sfSympalPlugin');
  }
}
