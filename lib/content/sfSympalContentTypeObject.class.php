<?php
/**
 * Represents a content type and uses a combination of sfSympalContentType
 * and app.yml as a data source.
 * 
 * @package     sfSympalPlugin
 * @subpackage  content
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfSympalContentTypeObject
{
  /**
   * @var string
   * @var array
   * @var sfSympalContentType
   */
  protected
    $_key,
    $_options,
    $_contentType;

  /**
   * @var sfRoute
   */
  protected $_routeObject;

  /**
   * Class constructor
   *
   * @param  string               $key          The unique identifying key for this type object
   * @param  array                $options      The options array sourced from app.yml
   * @param  sfSympalContentType  $contentType  An optional content type to use as a data source
   * @return void
   */
  public function __construct($key, $options, sfSympalContentType $contentType = null)
  {
    $this->_key = $key;
    $this->_options = $options;
    $this->_contentType = $contentType;
  }

  /**
   * Returns an option value by first looking at the normal configuration,
   * and then attempting to source from sfSympalContentType.
   *
   * @param  string $name   The option name
   * @param  mixed $default The default to return if the option doesn't exist
   * @return mixed
   */
  public function getOption($name, $default = null)
  {
    if ($value = $this->_getDataFromContentType($name))
    {
      return $value;
    }

    if (isset($this->_options[$name]))
    {
      return $this->_options[$name];
    }

    return $default;
  }

  /**
   * Retrieves the named data from the content type object if the content
   * type object exists and if the field exists on the content type record.
   *
   * @param  string $name The name of the data to retrieve
   * @return mixed
   */
  public function _getDataFromContentType($name)
  {
    if ($this->_contentType && $this->_contentType->contains($name))
    {
      return $this->_contentType->get($name);
    }
  }

  /**
   * Sets an option on this content type object. This won't set the value
   * on the related sfSympalContentType record.
   *
   * @param  string $name The name of the option to ste
   * @param  mixed $value The value to set on the option
   * @return void
   */
  public function setOption($name, $value)
  {
    $this->_options[$name] = $value;
  }

  /**
   * @return string
   */
  public function getKey()
  {
    return $this->_key;
  }

  /**
   * Returns the label to be used for this content type object
   *
   * @return string
   */
  public function getLabel()
  {
    return (string) $this->getOption('label', $this->getKey());
  }

  /**
   * Returns the array that defines the given rendering method
   *
   * @throws sfException
   * @param  string $name The name of the method
   * @return array
   */
  public function getRenderingMethod($name)
  {
    $renderingOptions = $this->getOption('rendering_methods', array());

    if (!isset($renderingOptions[$name]))
    {
      throw new sfException(sprintf('Invalid rendering method "%s" for content type "%s"', $name, $this->getKey()));
    }

    return $renderingOptions[$name];
  }

  /**
   * Returns whether or not the given rendering method exists
   *
   * @param  string $name The name of the rendering method
   * @return boolean
   */
  public function hasRenderingMethod($name)
  {
    $renderingOptions = $this->getOption('rendering_methods', array());

    return isset($renderingOptions[$name]);
  }

  /**
   * Returns the default rendering method array
   *
   * @return array
   */
  public function getDefaultRenderingMethod()
  {
    return $this->getRenderingMethod('default');
  }

  /**
   * @return string
   */
  public function getModel()
  {
    return (string) $this->getOption('model');
  }

  /**
   * Returns the route name that's used for this content type object
   *
   * @return string
   */
  public function getRouteName()
  {
    return $this->getKey();
  }

  /**
   * Returns the url/path used for the route that represents this content type
   *
   * @return string
   */
  public function getRoutePath()
  {
    $path = $this->getOption('default_path');
    if ($path != '/')
    {
      $path .= '.:sf_format';
    }

    return $path;
  }

  /**
   * Returns the actual route object for the route that represents this content type
   *
   * @return sfRoute
   */
  public function getRouteObject()
  {
    if (!$this->_routeObject)
    {
      $this->_routeObject = new sfRoute($this->getRoutePath(), array(
        'sf_format' => 'html',
        'sf_culture' => sfConfig::get('default_culture')
      ));
    }

    return $this->_routeObject;
  }
}