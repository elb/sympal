<?php

/**
 * Class for managing any miscellaneous cache 
 *
 *  * Routes
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalCacheManager
{
  protected
    $_dispatcher;
  
  protected
    $_helperAutoload = null,
    $_modules = null,
    $_layouts = null;

  /**
   * Instantiate the sfSympalCache instance and prime the cache for this Sympal
   * project
   *
   * @see sfSympalConfiguration
   * @see sfSympalPluginConfiguration
   * @param sfSympalConfiguration $sympalConfiguration
   */
  public function __construct(sfEventDispatcher $dispatcher, sfCache $cacheDriver = null)
  {
    $this->_dispatcher = $dispatcher;
    $this->_cacheDriver = $cacheDriver;

    $this->primeCache();
  }

  /**
   * Clear all the cache
   *
   * @return void
   */
  public function clean()
  {
    if ($this->getCacheDriver())
    {
      $this->getCacheDriver()->clean();
      $this->getCacheDriver()->set('primed', false);
    }
  }

  /**
   * Prime the cache for this sfSympalCache instance
   * 
   * Notifies the sympal.cache.prime event, allowing for any class to
   * hook in to the cache prime
   *
   * @param boolean $force Force it to prime the cache regardless of whether or not it has been primed already
   * @return void
   */
  public function primeCache($force = false)
  {
    // if no cache driver, we can't do anything
    if (!$this->getCacheDriver())
    {
      return;
    }

    if ($this->getCacheDriver()->get('primed') && !$force)
    {
      return;
    }

    $this->clean();

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.cache.prime'));

    $this->getCacheDriver()->set('primed', true);
  }

  /**
   * Reset the routing cache
   *
   * @return void
   */
  public function resetRouteCache()
  {
    $this->remove('routes.cache');

    $context = sfContext::getInstance();
    $configCache = $context->getConfigCache();

    if (file_exists($cachePath = $configCache->getCacheName('config/routing.yml')))
    {
      unlink($cachePath);
    }

    $context->getRouting()->loadConfiguration();
  }

  /**
   * @see sfCache::remove()
   */
  public function remove($key)
  {
    if ($this->getCacheDriver())
    {
      return $this->getCacheDriver()->remove($key);
    }
  }

  /**
   * @see sfCache::set()
   */
  public function set($key, $data, $lifeTime = null)
  {
    if ($this->getCacheDriver())
    {
      return $this->getCacheDriver()->set($key, serialize($data), $lifeTime);
    }
  }

  /**
   * @see sfCache::has()
   */
  public function has($key)
  {
    if ($this->getCacheDriver())
    {
      return $this->getCacheDriver()->has($key);
    }
  }

  /**
   * @see sfCache::get()
   */
  public function get($key)
  {
    if ($this->getCacheDriver())
    {
      return $this->getCacheDriver()->get($key);
    }
  }

  /**
   * @return sfCache
   */
  public function getCacheDriver()
  {
    return $this->_cacheDriver;
  }
}