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
   * Get the content routes yaml - used in routing.ml
   *
   * @return string $yaml
   */
  public static function getContentRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/content_routes.cache.yml';
    if (file_exists($cachePath) && sfConfig::get('sf_environment') !== 'test')
    {
      return file_get_contents($cachePath);
    }

    try {
      $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    sf_format: html
    sympal_content_type: %s
    sympal_content_type_id: %s
    sympal_content_id: %s
  class: sfDoctrineRoute
  options:
    model: sfSympalContent
    type: object
    method: getContent
    allow_empty: true
  requirements:
    sf_culture:  (%s)
    sf_format:   (%s)
    sf_method:   [post, get]
';

      $routes = array();
      $siteSlug = sfSympalConfig::getCurrentSiteName();

      if (!sfContext::hasInstance())
      {
        $configuration = ProjectConfiguration::getApplicationConfiguration(sfConfig::get('sf_app'), 'prod', false);
        sfContext::createInstance($configuration);
      }

      /*
       * Step 1) Process all sfSympalContent records with a custom_path,
       *         module, or action. These have sympal_content_* routes
       */
      $contents = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->innerJoin('c.Site s')
        ->where("(c.custom_path IS NOT NULL AND c.custom_path != '') OR (c.module IS NOT NULL AND c.module != '') OR (c.action IS NOT NULL AND c.action != '')")
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();
      foreach ($contents as $content)
      {
        $routes['content_'.$content->getId()] = sprintf($routeTemplate,
          substr($content->getRouteName(), 1),
          $content->getRoutePath(),
          $content->getModuleToRenderWith(),
          $content->getActionToRenderWith(),
          $content->Type->name,
          $content->Type->id,
          $content->id,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      /*
       * Step 2) Create a route for each sfSympalContentType record
       */
      $contentTypes = Doctrine::getTable('sfSympalContentType')
        ->createQuery('t')
        ->execute();
      foreach ($contentTypes as $contentType)
      {
        $routes['content_type_'.$contentType->getId()] = sprintf($routeTemplate,
          substr($contentType->getRouteName(), 1),
          $contentType->getRoutePath(),
          $contentType->getModuleToRenderWith(),
          $contentType->getActionToRenderWith(),
          $contentType->name,
          $contentType->id,
          null,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);

      return $routes;
    }
    catch (Exception $e)
    {
      // for now, I'd like to not obfuscate the errors - rather report them
      throw $e;
    }
  }

  /**
   * Render a formatted exception message. Used when rendering content
   * and something goes wrong.
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
   * Get a symfony resource (partial or component)
   * 
   * This basically looks first for a component defined by the given module
   * and action. If one doesn't exist, it then looks for a partial matching
   * the module and action pair.
   *
   * @param string $module The module name
   * @param string $action The partial, or component name
   * @param array $variables Te variables to pass into the component or partial
   * 
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
}
