<?php

/**
 * Main Plugin configuration class for sympal.
 * 
 * This is responsible for loading in plugins that are core to sympal
 * 
 * @package     sfSympalPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @var sfContext
   * @var sfSympalSiteManager
   */
  protected
    $_context,
    $_siteManager,
    $_cacheManager;

  /**
   * sfSympalPlugin version number
   */
  const VERSION = '1.0.0-ALPHA5';

  /**
   * Initializes this plugin
   */
  public function initialize()
  {
    /*
     * We disable symfony autoload again feature because it is too slow in dev mode
     * If you introduce a new class when using sympal you just must clear your
     * cache manually
     */
    sfAutoloadAgain::getInstance()->unregister();

    // mark classes as safe from the output escaper
    self::_markClassesAsSafe();

    // Actually bootstrap sympal
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrapContext'));
  }

  /**
   * Listens to the context.load_factories event and creates the sympal context
   */
  public function bootstrapContext(sfEvent $event)
  {
    $this->_context = $event->getSubject();

    // register the extending actions class
    $class = sfConfig::get('app_sympal_config_extended_actions_class', 'sfSympalActions');
    $actions = new $class();
    $this->dispatcher->connect('component.method_not_found', array($actions, 'extend'));

    // register the template.filter_parameters event
    $this->dispatcher->connect('template.filter_parameters', array($this, 'filterTemplateParameters'));

    // create the cache manager instance
    $this->_cacheManager = $this->_createCacheManager();

    // throw the sympal load event
    $this->dispatcher->notify(new sfEvent($this, 'sympal.load', array()));
  }

  /**
   * @TODO How does this compare with the variables passed to the view
   * via sfSympalContentRenderer. This seems more all-encompassing, but
   * still possibly redundant.
   */
  public function filterTemplateParameters(sfEvent $event, $parameters)
  {
    $parameters['sf_sympal_context'] = $this;

    // Don't override the variable if it's not set
    if (!isset($parameters['sf_sympal_site']))
    {
      $parameters['sf_sympal_site'] = $this->getSiteManager()->getSite();
    }

    return $parameters;
  }

  /**
   * Creates a new instance of sfSympalCacheManager from the config
   *
   * @return sfSympalCacheManager
   */
  protected function _createCacheManager()
  {
    $cacheConfig = sfSympalConfig::get('cache_driver');
    if ($cacheConfig['enabled'])
    {
      $class = $cacheConfig['class'];
      $options = $cacheConfig['options'];
      $cacheDriver = new $class($options);
    }
    else
    {
      $cacheDriver = null;
    }

    $class = sfConfig::get('app_sympal_config_cache_manager_class', 'sfSympalCacheManager');

    return new $class($this->dispatcher, $cacheDriver);
  }

  /**
   * Returns the current site manager
   *
   * @return sfSympalSiteManager
   */
  public function getSiteManager()
  {
    if ($this->_siteManager === null)
    {
      $this->_siteManager = new sfSympalSiteManager($this->configuration);
    }

    return $this->_siteManager;
  }

  /**
   * @return sfSympalCacheManager
   */
  public function getCacheManager()
  {
    return $this->_cacheManager;
  }

  /**
   * Get a sfSympalContentRenderer instance for a given sfSympalContent instance
   *
   * @param sfSympalContent $content The sfSympalContent instance
   * @param string $format Optional format to render
   * @return sfSympalContentRenderer $renderer
   */
  public function getContentRenderer(sfSympalContent $content, $format)
  {
    return new sfSympalContentRenderer($this->_context, $content, $format);
  }

  /**
   * Mark necessary sympal classes as safe
   * 
   * These classes won't be wrapped with the output escaper
   * 
   * @todo Put the rest of these in the correct plugin
   *
   * @return void
   */
  private static function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalContent',
      'sfSympalContentTranslation',
      'sfSympalContentRenderer',
    ));
  }
}
