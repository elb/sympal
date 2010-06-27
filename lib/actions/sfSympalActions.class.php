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
   * Shortcut to reset the sympal routes cache from your actions
   *
   * @return void
   */
  public function resetSympalRoutesCache()
  {
    // Reset the routes cache incase of the url changing or a custom url was added
    return $this->getSympalContext()->getService('cache_manager')->resetRouteCache();
  }

  /**
   * Shortcut to the clear cache task from your actions
   *
   * @param array $options 
   * @return void
   */
  public function clearCache(array $options = array())
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->getContext()->getEventDispatcher(), new sfFormatter());
    $task->run(array(), $options);

    $this->resetSympalRoutesCache();
  }

  /**
   * Get instance of the sfSympalContentActionLoader for loading and rendering content
   *
   * @return sfSympalContentActionLoader
   */
  public function getSympalContentActionLoader()
  {
    return new sfSympalContentActionLoader($this->getSubject());
  }
}
