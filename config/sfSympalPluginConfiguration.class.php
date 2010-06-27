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
  protected
    $_sympalContext;

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
     * We disable Symfony autoload again feature because it is too slow in dev mode
     * If you introduce a new class when using sympal you just must clear your
     * cache manually
     */
    sfAutoloadAgain::getInstance()->unregister();

    // mark classes as safe from the output escaper
    self::_markClassesAsSafe();

    // Actually bootstrap sympal
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrapContext'));

    // Connect to the sympal post-load event
    $this->dispatcher->connect('sympal.load', array($this, 'configureSympal'));
    
    /*
     * Initialize some symfony config.
     * 
     * Must be here (and not as a listener to sympal.load) so that it acts
     * before the theme manager has a chance to set any themes
     */
    $this->_initializeSymfonyConfig();
  }


  /**
   * Returns the sfSympalConfiguration object
   * 
   * @return sfSympalConfiguration
   */
  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  /**
   * Listens to the context.load_factories event and creates the sympal context
   */
  public function bootstrapContext(sfEvent $event)
  {
    $this->_sympalContext = sfSympalContext::createInstance($event->getSubject(), $this->getSympalConfiguration());
  }

  /**
   * Listens to the sympal.load event
   */
  public function configureSympal(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    
    // @todo this should be broken up, possibly moved, removed
    $this->configuration->loadHelpers(array(
      'Sympal',
      'SympalContentSlot',
      'SympalPager',
    ));

    // Add listener on template.filter_parameters to add sf_sympal_site var to view
    $site = $this->_sympalContext->getService('site_manager');
    $this->dispatcher->connect('template.filter_parameters', array($site, 'filterTemplateParameters'));
  }

  /**
   * Mark necessary Sympal classes as safe
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
      'sfSympalContentSlot',
      'sfSympalContentSlotTranslation',
      'sfSympalContentRenderer',
    ));
  }
}
