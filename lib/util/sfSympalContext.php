<?php

/**
 * Context class for a Sympal instance
 * 
 * A Sympal "context" is a singleton with respect to an individual sfSympalSite
 * record. This is very similar to sfContext, which is a singleton with respect
 * to each symfony app.
 * 
 * If some object has a dependency on a symfony app but NOT an sfSympalSite
 * record, then it should be handled by sfContext. If it DOES have a
 * dependency on the current sfSympalSite record, it'll be handled here
 * on the sfSympalContext instance.
 * 
 * This manages things such as
 *   * The current sfSympalSite object
 *   * The current menu item
 *   * The current content object (sfSympalContent)
 * 
 * @package     sfSympalPlugin
 * @subpackage  util
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;
  
  protected
    $_dispatcher,
    $_sympalConfiguration,
    $_symfonyContext;
  
  protected
    $_currentMenuItem,
    $_currentContent;

  /**
   * Class constructor
   * 
   * @param sfSympalConfiguration $sympalConfiguration The Sympal configuration
   * @param sfContext $symfonyContext The symfony context
   */
  public function __construct(sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_dispatcher = $symfonyContext->getEventDispatcher();
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
    
    $this->initialize();
  }

  /**
   * Initializes sympal
   */
  protected function initialize()
  {
    // register some listeners
    $this->_registerExtendingClasses();
    $this->_registerListeners();

    // notify that sympal is done bootstrapping
    $this->_dispatcher->notify(new sfEvent($this, 'sympal.load', array()));
  }

  /**
   * Registers certain classes that extend core symfony classes
   */
  protected function _registerExtendingClasses()
  {
    // extend the component/action class
    $class = sfConfig::get('app_sympal_extended_actions_class', 'sfSympalActions');
    $actions = new $class();
    $actions->setSympalContext($this);
    
    $this->_dispatcher->connect('component.method_not_found', array($actions, 'extend'));
  }

  /**
   * Registeres needed event listeners
   */
  protected function _registerListeners()
  {
    $this->_dispatcher->connect('template.filter_parameters', array($this, 'filterTemplateParameters'));
  }

  /**
   * Get the current sfSympalConfiguration instance
   *
   * @return sfSympalConfiguration $sympalConfiguration
   */
  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  /**
   * Get the current sfContext instance
   *
   * @return sfContext $symfonyContext
   */
  public function getSymfonyContext()
  {
    return $this->_symfonyContext;
  }

  public function getApplicationConfiguration()
  {
    return $this->getSympalConfiguration()->getProjectConfiguration();
  }

  /**
   * Get a sfSympalContentRenderer instance for a given sfSympalContent instance
   *
   * @param sfSympalContent $content The sfSympalContent instance
   * @param string $format Optional format to render
   * @return sfSympalContentRenderer $renderer
   */
  public function getContentRenderer(sfSympalContent $content, $format = null)
  {
    return new sfSympalContentRenderer($this, $content, $format);
  }

  /**
   * @TODO How does this compare with the variables passed to the view
   * via sfSympalContentRenderer. This seems more all-encompassing, but
   * still possibly redundant.
   */
  public function filterTemplateParameters(sfEvent $event, $parameters)
  {
    $parameters['sf_sympal_context'] = $this;

    return $parameters;
  }

  /**
   * Get a sfSympalContext instance
   *
   * @param string $site Optional site/app name to get
   * @return sfSympalContext $sympalContext
   */
  public static function getInstance($site = null)
  {
    if (is_null($site))
    {
      if (!self::$_current)
      {
        throw new InvalidArgumentException('Could not find a current sympal context instance');
      }
      return self::$_current;
    }

    if (!isset(self::$_instances[$site]))
    {
      throw new sfException($site.' instance does not exist.');
    }
    return self::$_instances[$site];
  }

  /**
   * Check if we have a sfSympalContext yet
   *
   * @param string $site Optional site/app name to check for
   * @return boolean
   */
  public static function hasInstance($site = null)
  {
    return is_null($site) ? !empty(self::$_instances) : isset(self::$_instances[$site]);
  }

  /**
   * Create a new sfSympalContext instance for a given sfContext and sfSympalConfiguration instance
   *
   * @param sfContext $symfonyContext 
   * @param sfSympalConfiguration $sympalConfiguration 
   * @return sfSympalContext $sympalContext
   */
  public static function createInstance(sfContext $symfonyContext, sfSympalConfiguration $sympalConfiguration)
  {
    $name = $symfonyContext->getConfiguration()->getApplication();

    $instance = new self($sympalConfiguration, $symfonyContext);
    self::$_instances[$name] = $instance;
    self::$_current = $instance;

    return self::$_instances[$name];
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.context.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }  
}