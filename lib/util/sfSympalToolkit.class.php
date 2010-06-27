<?php

/**
 * Toolkit class for general Sympal helper methods
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalToolkit
{

  /**
   * Load the given helpers
   *
   * @param string $helpers 
   * @return void
   */
  public static function loadHelpers($helpers)
  {
    sfApplicationConfiguration::getActive()->loadHelpers($helpers);
  }

  /**
   * Render a formatted exception message
   *
   * @param Exception $e 
   * @return string $html
   */
  public static function renderException(Exception $e)
  {
    return get_partial('sympal_default/exception', array('e' => $e));
  }

  /**
   * Get the default application by find the first app in the apps directory
   *
   * @return string $appName
   */
  public static function getDefaultApplication()
  {
    $apps = glob(sfConfig::get('sf_root_dir').'/apps/*');
    foreach ($apps as $app)
    {
      $info = pathinfo($app);
      $path = $app.'/config/'.$info['filename'].'Configuration.class.php';
      require_once $path;
      $reflection = new ReflectionClass($info['filename'].'Configuration');
      if (!$reflection->getConstant('disableSympal'))
      {
        return $info['filename'];
      }
    }

    return 'sympal';
  }

  /**
   * Check all the requirements for installing Sympal
   *
   * @return void
   * @throws sfException if a requirement is not met
   */
  public static function checkRequirements()
  {
    $user = sfContext::getInstance()->getUser();
    $app = sfConfig::get('sf_app');
    if (!$user instanceof sfSympalUser)
    {
      throw new sfException('myUser class located in '.sfConfig::get('sf_root_dir').'/apps/'.$app.'/myUser.class.php must extend sfSympalUser');
    }

    $routingPath = sfConfig::get('sf_root_dir').'/apps/'.$app.'/config/routing.yml';
    $routes = sfYaml::load(file_get_contents($routingPath));
    if (isset($routes['homepage']) || isset($routes['default_index']))
    {
      throw new sfException('Your application routing file must not have a homepage, default, or default_index route defined.');
    }

    $databasesPath = sfConfig::get('sf_config_dir').'/databases.yml';
    if(stristr(file_get_contents($databasesPath), 'propel'))
    {
      throw new sfException('Your project databases.yml must be configured to use Doctrine and not Propel.');
    }

    $apps = glob(sfConfig::get('sf_root_dir').'/apps/*');
    if (empty($apps))
    {
      throw new sfException('You must have at least one application created in order to use Sympal.');
    }
  }

  /**
   * Get a symfony resource (partial or component)
   * 
   * This basically looks first for a component defined by the given module
   * and action. If one doesn't exist, it then looks for a partial matching
   * the module and action pair.
   *
   * @param string $module 
   * @param string $action 
   * @param array $variables 
   * @return string $html
   */
  public static function getSymfonyResource($module, $action = null, $variables = array())
  {
    if (strpos($module, '/'))
    {
      $variables = (array) $action;
      $e = explode('/', $module);
      list($module, $action) = $e;
    }

    $context = sfContext::getInstance();
    $context->getConfiguration()->loadHelpers('Partial');
    $controller = $context->getController();

    if ($controller->componentExists($module, $action))
    {
      return get_component($module, $action, $variables);
    }
    else
    {
      return get_partial($module.'/'.$action, $variables);
    }

    throw new sfException('Could not find component or partial for the module "'.$module.'" and action "'.$action.'"');
  }

  /**
   * Check if a module and action exist
   *
   * @param string $moduleName 
   * @param string $actionName 
   * @return void
   * @author Jonathan Wage
   */
  public static function moduleAndActionExists($moduleName, $actionName)
  {
    $modulePath = sfConfig::get('sf_apps_dir').'/'.sfConfig::get('sf_app').'/modules/'.$moduleName.'/actions/actions.class.php';
    if (file_exists($modulePath))
    {
      return strpos(file_get_contents($modulePath), 'public function execute'.ucfirst($actionName)) !== false ? true : false;
    }
    else
    {
      return false;
    }
  }

  /**
   * Get all available language codes/flags
   *
   * @return array $codes
   */
  public static function getAllLanguageCodes()
  {
    $flags = sfFinder::type('file')
      ->in(sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getRootDir().'/web/images/flags');

    $codes = array();
    foreach ($flags as $flag)
    {
      $info = pathinfo($flag);
      $codes[] = $info['filename'];
    }
    return $codes;
  }

  /**
   * Deletes a symfony application and all files associated with it
   * 
   * @param string $app The name of the application
   * @return null
   */
  public static function deleteApplication($app)
  {
    // application itself (apps/$app)
    $appsDir = sfConfig::get('sf_apps_dir') . DIRECTORY_SEPARATOR . $app;
    sfToolkit::clearDirectory($appsDir);
    if (is_writable($appsDir)) rmdir($appsDir);

    // public files (web/$app_*.php)
    $pubPref = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $app;
    if (is_writeable($pubPref . '_dev.php')) unlink($pubPref . '_dev.php');
    if (is_writeable($pubPref . '.php'))     unlink($pubPref . '.php');

    // fixtures (data/fixtures/sympal/$app)
    $fixtDir = implode(DIRECTORY_SEPARATOR, array(sfConfig::get('sf_data_dir'), 'fixtures', 'sympal', $app));
    sfToolkit::clearDirectory($fixtDir);
    if (is_writable($fixtDir)) rmdir($fixtDir);
  }
}
