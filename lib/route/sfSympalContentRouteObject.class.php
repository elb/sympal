<?php

/**
 * Class responsible for generating the route information for a sfSympalContent
 * instance. Abstracted to this class so it can be used standalone and cached 
 * alone from the sfSympalContentObject instance
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalContentRouteObject
{
  protected
    $_content,
    $_routeName,
    $_routePath,
    $_routeObject,
    $_routeValues;

  public function __construct(sfSympalContent $content)
  {
    $this->compile($content);
  }

  /**
   * Compile all the information for the given sfSympalContent instance
   *
   * @param sfSympalContent $content
   * @return void
   */
  public function compile(sfSympalContent $content)
  {
    $this->_routeName = $this->_buildRouteName($content);
    $this->_routePath = $this->_buildRoutePath($content);
    $this->_routeObject = $this->_buildRouteObject($content);
    $this->_routeValues = $this->_buildRouteValues($content);
  }

  /**
   * Get the complete route for content
   *
   * @return string $route
   */
  public function getRoute()
  {
    $values = $this->getCultureRouteValues();

    if (!empty($values))
    {
      return $this->_routeName.'?'.http_build_query($values);
    }
    else
    {
      return $this->_routeName;
    }
  }

  /**
   * Get the name of the route.
   *
   * @return string $routeName
   */
  public function getRouteName()
  {
    return $this->_routeName;
  }

  /**
   * Get the route path. i.e. /route/path/:slug
   *
   * @return string
   * @author Jonathan Wage
   */
  public function getRoutePath()
  {
    return $this->_routePath;
  }

  /**
   * Get the sfRoute object that represents this route path
   *
   * @return sfRoute $routeObject
   */
  public function getRouteObject()
  {
    return $this->_routeObject;
  }

  /**
   * Get the array of values used to generates routes for this content
   *
   * @return array $routeValues
   */
  public function getRouteValues()
  {
    return $this->_routeValues;
  }

  /**
   * Get the array of values for the current culture used to generate routes for this content
   *
   * @param string $culture Optional culture to return, otherwise it uses the current culture
   * @return array $routeValues
   */
  public function getCultureRouteValues($culture = null)
  {
    if ($culture === null)
    {
      $culture = $this->getCurrentCulture();
    }

    return $culture && isset($this->_routeValues[$culture]) ? $this->_routeValues[$culture] : current($this->_routeValues);
  }

  /**
   * Get the current culture
   *
   * @return string $culture
   */
  public function getCurrentCulture()
  {
    if ($user = sfContext::getInstance()->getUser())
    {
      return $user->getCulture();
    }
    else
    {
      return sfConfig::get('sf_default_culture');
    }
  }

  /**
   * Get the evaluated route path. i.e. if you have /route/path/:slug
   * and your slug value was `my_slug` the evaluated route path would be /route/path/my_slug
   *
   * @return string $evaluatedRoutePath
   */
  public function getEvaluatedRoutePath()
  {
    $values = $this->getCultureRouteValues();
    $values['sf_culture'] = $this->getCurrentCulture();

    return $this->getRouteObject()->generate($values);
  }

  /**
   * Build the array of all culture values for the given content record
   *
   * This attempts to retrieve each variable first off of each Translation
   * (if 18n enabled) record, with a fallback to the normal record.
   *
   * @param sfSympalContent $content 
   * @return array $routeValues
   */
  protected function _buildRouteValues(sfSympalContent $content)
  {
    $variables = $this->getRouteObject()->getVariables();
    $isI18nEnabled = sfSympalConfig::isI18nEnabled();

    $languageCodes = $isI18nEnabled ? sfSympalConfig::getLanguageCodes() : array($this->getCurrentCulture());
    if (!is_array($languageCodes))
    {
      throw new sfException(sprintf('Language codes is not an array: "%s" given', $languageCodes));
    }
    
    $values = array();
    foreach ($languageCodes as $code)
    {
      foreach (array_keys($variables) as $name)
      {
        if ($content->getTable()->hasField($name))
        {
          if ($isI18nEnabled && isset($content->Translation[$code]->$name))
          {
            $values[$code][$name] = $content->Translation[$code]->$name;
          }
          else
          {
            $values[$code][$name] = $content->$name;
          }
        }
        elseif ($content->Record->getTable()->hasField($name))
        {
          if ($isI18nEnabled && isset($content->Record->Translation[$code]->$name))
          {
            $values[$code][$name] = $content->Record->Translation[$code]->$name;
          }
          else
          {
            $values[$code][$name] = $content->Record->$name;
          }
        }
        else if (method_exists($content, $method = 'get'.sfInflector::camelize($name)))
        {
          $values[$code][$name] = $content->$method();
        }
      }
    }

    return $values;
  }

  /**
   * Build the route name for the given content record
   *
   * @param sfSympalContent $content 
   * @return string $routeName
   */
  protected function _buildRouteName(sfSympalContent $content)
  {
    if ($content->get('custom_path', false) || $content->get('rendering_method', false))
    {
      // The homepage "/" receives special consideration
      if ($content->get('custom_path') == '/')
      {
        return 'homepage';
      }

      // if custom_path OR module OR action, we have a sympal_content_% route
      return 'sympal_content_' . $content->id;
    }
    else
    {
      return $content->Type->getTypeObject()->getRouteName();
    }
  }

  /**
   * Build the sfRoute object for the given content record
   *
   * @param sfSympalContent $content 
   * @return sfRoute $routeObject
   */
  protected function _buildRouteObject(sfSympalContent $content)
  {
    // Generate a route object for this content only if it has a custom path
    if ($content->custom_path)
    {
      return new sfRoute($this->getRoutePath(), array(
        'sf_format' => 'html',
        'sf_culture' => sfConfig::get('default_culture')
      ));
    // Otherwise get it from the content type
    }
    else
    {
      return $content->Type->getTypeObject()->getRouteObject();
    }
  }

  /**
   * Build the route path for the given content record
   *
   * @param sfSympalContent $content 
   * @return string $routePath
   */
  protected function _buildRoutePath(sfSympalContent $content)
  {
    // If content has a custom path then lets use it
    if ($content->custom_path)
    {
      $path = $content->custom_path;
      if ($path != '/')
      {
        $path .= '.:sf_format';
      }

      return $path;
    }
    /*
     * If content has a custom module or action then we need a sympal_content_*
     * route for it.
     * 
     * This means that we need to determine its final url as if it had
     * a custom_path url. Specifically, instead of reporting back
     * "/blog/:slug" we need to report back the exact "/blog/my-post".
     * 
     * Unfortunately, the only way I can really see to do this is by
     * cloning the Content record and creating a whole other content route
     * object where we ask it to determine what its real url would have been
     * had there been more custom module and action.
     */
    else if ($content->get('rendering_method', false))
    {
      $renderingMethod = $content->rendering_method;
      $content->rendering_method = null;
      
      $routePath = $content->getContentRouteObject()->getEvaluatedRoutePath();
      $content->rendering_method = $renderingMethod;

      return $routePath;
    }
    // Otherwise fallback and get route path from the content type
    else
    {
      return $content->Type->getTypeObject()->getRoutePath();
    }
  }
}
