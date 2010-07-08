<?php

/**
 * Represents a content object and holds the logic for merging
 * configuration between sfsympalContent and its content type object
 * 
 * @package     sfSympalPlugin
 * @subpackage  content
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */

class sfSympalContentObject
{

  /**
   * @var sfSympalContent
   * @var sfSympalContentTypeObject
   */
  protected
    $_contentRecord,
    $_typeObject;

  /**
   * @var sfSympalContentRouteObject
   */
  protected
    $_contentRouteObject;

  /**
   * Class constructor
   *
   * @param sfSympalContent $content The content record to source data from
   * @param sfSympalContentTypeObject $type The related content type object
   * @params array $options An array of options
   * @return void
   */
  public function __construct(sfSympalContent $contentRecord, sfSympalContentTypeObject $type)
  {
    $this->_contentRecord = $contentRecord;
    $this->_typeObject = $type;
  }

  /**
   * Returns the rendering method array to use for this content
   *
   * @return array
   */
  public function getRenderingMethod()
  {
    // Retrieve the rendering method. Note that if the rendering method
    // saved in the database is not found, it silently falls to the default.
    if ($this->_typeObject->hasRenderingMethod($this->_contentRecord['rendering_method']))
    {
      return $this->_typeObject->getRenderingMethod($this->_contentRecord['rendering_method']);
    }
    else
    {
      return $this->_typeObject->getDefaultRenderingMethod();
    }
  }

  /**
   * @return sfSympalContent
   */
  public function getContentRecord()
  {
    return $this->_contentRecord;
  }

  /**
   * @return sfSympalContentTypeObject
   */
  public function getTypeObject()
  {
    return $this->_typeObject;
  }

  /**
   * Returns the route object related to this content record
   *
   * @return sfSympalContentRouteObject
   */
  public function getContentRouteObject()
  {
    if (!$this->_contentRouteObject)
    {
      $this->_contentRouteObject = new sfSympalContentRouteObject($this);
    }

    return $this->_contentRouteObject;
  }

  /**
   * Returns the url to this content
   *
   * @param array $options The array of url options
   * @return string
   */
  public function getUrl($options = array())
  {
    return sfContext::getInstance()->getController()->genUrl($this->getRoute(), $options);
  }
}