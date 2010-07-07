<?php

/**
 * Main configuration class for sympal
 * 
 * @package     sfSympalPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_sympalContext,
    $_bootstrap,
    $_allManageablePlugins,
    $_contentTypePlugins;
  
  protected
    $_plugins,
    $_pluginPaths;
  
  /**
   * Information that is generated and cached
   */
  protected
    $_modules = null;
  
  protected
    $_cacheManager;

  public function __construct(ProjectConfiguration $projectConfiguration)
  {
    $this->_projectConfiguration = $projectConfiguration;
    $this->_dispatcher = $projectConfiguration->getEventDispatcher();
  }

  /**
   * Get array of all "Sympal" plugins
   *
   * @return array $plugins
   */
  public function getPlugins()
  {
    if ($this->_plugins === null)
    {
      $this->_plugins = array_keys($this->getPluginPaths());
    }

    return $this->_plugins;
  }

  /**
   * Get paths to all Sympal plugins
   * 
   * A Sympal plugin is defined as any that contains "sfSympal" in its name
   *
   * @return array $pluginPaths
   */
  public function getPluginPaths()
  {
    if ($this->_pluginPaths === null)
    {
      $pluginPaths = $this->getProjectConfiguration()->getAllPluginPaths();
      $this->_pluginPaths = array();
      foreach ($pluginPaths as $pluginName => $path)
      {
        if (strpos($pluginName, 'sfSympal') !== false)
        {
          $this->_pluginPaths[$pluginName] = $path;
        }
      }
    }

    return $this->_pluginPaths;
  }

  /**
   * Returns a value from the cache object (if there is one)
   * 
   * @param string $name The name/key of the cache to return
   * @param mixed $default The default value to return if the cache isn't found
   */
  public function getCache($name = null, $default = null)
  {
    if ($name === null)
    {
      throw new sfException('getCache() is deprecated, use getCacheManager()');
    }

    return $this->getCacheManager() ? $this->getCacheManager()->get($name, $default) : $default;
  }

  /**
   * Set a value to the cache (if there is a cache object
   * 
   * @param string $name The name/key of the cache to set
   * @param mixed $value The value to set to the cache
   * 
   * @return boolean Whether or not the cache was set
   */
  public function setCache($name, $value)
  {
    return $this->getCacheManager() ? $this->getCacheManager()->set($name, $value) : false;
  }

  /**
   * Returns the event dispatcher
   * 
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->_dispatcher;
  }
}