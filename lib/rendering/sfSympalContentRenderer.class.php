<?php
/**
 * Renders a sfSympalContent object per the template rules.
 *
 * This would be used by calling ->render() in the template
 * 
 * @package     sfSympalPlugin
 * @subpackage  rendering
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfSympalContentRenderer
{
  /**
   * @var sfEventDispatcher
   * @var sfSympalContent
   * @var string
   * @var array
   */
  protected
    $_dispatcher,
    $_content,
    $_format,
    $_renderVariables;

  /**
   * Class constructor
   *
   * @param sfEventDispatcher $dispatcher
   * @param sfSympalContent $content
   * @param string $format
   * @return void
   */
  public function __construct(sfEventDispatcher $dispatcher, sfSympalContent $content, $format = null)
  {
    $this->_dispatcher = $dispatcher;
    $this->_content = $content;
    $this->_format = $format ? $format : 'html';
  }

  /**
   * Returns the format
   *
   * @return string
   */
  public function getFormat()
  {
    return $this->_format;
  }

  /**
   * @param  string $format The format for rendering the content
   * @return void
   */
  public function setFormat($format)
  {
    $this->_format = $format;
  }

  /**
   * Returns the variables that should be made available in the template
   *
   * @return array
   */
  public function getRenderVariables()
  {
    if ($this->_renderVariables === null)
    {
      $this->_renderVariables = array(
        'sf_format'   => $this->_format,
        'sf_sympal_content' => $this->_content,
        sfInflector::tableize(get_class($this->_content->Record)) => $this->_content->Record, 
      );

      $this->_renderVariables = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_variables'), $this->_renderVariables)->getReturnValue();
    }

    return $this->_renderVariables;
  }

  /**
   * Renders this content.
   *
   * If the format is html, this renders via the correct template/partial,
   * and also throws a sympal.content_renderer.filter_content event.
   *
   * If the format is not html, renderNonHtmlFormats is called.
   *
   * @return string
   */
  public function render()
  {
    $variables = $this->getRenderVariables();

    if ($this->_format == 'html')
    {
      $return = sfSympalToolkit::getSymfonyResource($this->_content->getTemplateToRenderWith(), $variables);
      $return = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_content', $variables), $return)->getReturnValue();
    }
    else
    {
      $return = $this->renderNonHtmlFormats();
    }

    return $return;
  }

  /**
   * Renders the content for non-html formats.
   *
   * @throws RuntimeException
   * @return string
   */
  public function renderNonHtmlFormats()
  {
    switch ($this->_format)
    {
      case 'xml':
      case 'json':
      case 'yml':
        $return = $this->_content->getFormatData($this->_format);
      default:
        $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.content_renderer.unknown_format', $this->getRenderVariables()));

        if ($event->isProcessed())
        {
          $this->setFormat($event['sf_format']);
          $return = $event->getReturnValue();
        }
    }

    if (isset($return) && $return)
    {
      return $return;
    }
    else
    {
      throw new RuntimeException(sprintf('Unknown render format: "%s"', $this->_format));
    }
  }

  /**
   * Renders the content or renders an exception if one is thrown.
   *
   * @return string
   */
  public function __toString()
  {
    try
    {
      return (string) $this->render();
    }
    catch (Exception $e)
    {
      return sfSympalToolkit::renderException($e);
    }
  }
}