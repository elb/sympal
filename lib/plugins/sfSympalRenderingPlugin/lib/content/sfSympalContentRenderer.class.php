<?php

class sfSympalContentRenderer
{
  protected
    $_context,
    $_configuration,
    $_dispatcher,
    $_menuItem,
    $_content,
    $_format,
    $_renderVariables = array();

  public function __construct(sfContext $context, sfSympalContent $content, $format = null)
  {
    $this->_context = $context;
    $this->_configuration = $this->_symfonyContext->getConfiguration();
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_configuration->loadHelpers(array('Tag', 'Url', 'Partial'));
    $this->_content = $content;
    $this->_menuItem = $this->_content->getMenuItem();
    $this->_format = $format ? $format : 'html';
  }

  public function getFormat()
  {
    return $this->_format;
  }

  public function setFormat($format)
  {
    $this->_format = $format;
  }

  public function getRenderVariables()
  {
    if (!$this->_renderVariables)
    {
      $this->_renderVariables = array(
        'sf_format'   => $this->_format,
        'content'  => $this->_content,
        'sf_sympal_content' => $this->_content,  // duplicated because I can't decide on one
        'menuItem' => $this->_menuItem,
      );

      $this->_renderVariables = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_variables'), $this->_renderVariables)->getReturnValue();
    }
    return $this->_renderVariables;
  }

  public function render()
  {
    $this->_format = $this->_format ? $this->_format : 'html';
    $variables = $this->getRenderVariables();

    if ($this->_format == 'html')
    {
      $return = sfSympalToolkit::getSymfonyResource($this->_content->getTemplateToRenderWith(), $variables);
      $return = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_content', $variables), $return)->getReturnValue();
    } else {
      $return = $this->renderNonHtmlFormats();
    }
    return $return;
  }

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
    } else {
      throw new RuntimeException(sprintf('Unknown render format: "%s"', $this->_format));
    }
  }

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