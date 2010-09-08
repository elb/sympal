<?php

/**
 * Used the retrieve the sfSympalContent record for an action and initialize
 * it into the system, performing actions like:
 *
 *  * Setting the current content and site on the site manager
 *  * Setting the page title, metadata
 *  * Handling unpublished content
 * 
 * @package     sfSympalPlugin
 * @subpackage  rendering
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-31
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentActionLoader
{
  protected
    $_actions,
    $_content;

  /**
   * Class constructor
   *
   * @param sfActions $actions The actions class
   * @param sfSympalContent $content The optional content record
   * @return void
   */
  public function __construct(sfActions $actions, sfSympalContent $content = null)
  {
    $this->_actions = $actions;

    if ($content !== null)
    {
      $this->_content = $content;
    }

    $this->_initializeContent();
  }

  /**
   * Returns the current sfSympalContent record.
   *
   * Also sets the current content and site on the site manager service.
   *
   * @return sfSympalContent
   */
  public function getContent()
  {
    if ($this->_content === null)
    {
      $contentObject = $this->_actions->getRoute()->getObject();

      if (!$contentObject)
      {
        throw new sfError404Exception('No object route found');
      }

      $this->_content = $contentObject->Content;
    }

    return $this->_content;
  }

  /**
   * Sets up the current set and content object on the current configuration
   */
  protected function _initializeContent()
  {
    $siteManager = $this->_getSympalConfiguration()->getSiteManager();

    $siteManager->setCurrentContent($this->getContent());
    $siteManager->setSite($this->getContent()->getSite());
  }

  /**
   * Loads and processes the sfSympalContent record associated with this action:
   *
   *  * Fetches the sfSympalContent record
   *  * Handles the 404 if necessary
   *  * Handles unpublished content
   *  * Sets up the metadata (page title, etc)
   *  * Throws a sympal.load_content event
   *
   * @todo Replace the security check
   *
   * @return sfSympalContent
   */
  public function loadContent()
  {
    $this->_handleIsPublished();
    //$this->_user->checkContentSecurity($content);

    $this->_loadMetaData();

    // throw the sympal.load_content event
    $this->_actions->getContext()->getEventDispatcher()->notify(new sfEvent(
      $this,
      'sympal.load_content',
      array('content' => $this->getContent())
    ));
  }

  /**
   * The main method that loads the bound content object and forces things
   * such as the 404, is_published, metadata, etc.
   *
   * You can optionally pass in the content record or this will try to
   * get it from the object bound to the route.
   *
   * @param bool $fakeHtmlRequest Whether to fake an html request. Should
   *                              usually be left as false.
   * @return sfSympalContentRenderer
   */
  public function loadContentRenderer($fakeHtmlRequest = false)
  {
    // load and initialize the content
    $this->loadContent();

    // get the renderer
    $renderer = $this->_getSympalConfiguration()
      ->getContentRenderer(
        $this->getContent(),
        $this->_actions->getRequest()->getRequestFormat()
      );

    if ($fakeHtmlRequest && $renderer->getFormat() != 'html')
    {
      $this->fakeHtmlRequest();
    }

    return $renderer;
  }

  /**
   * Allows the request to think it has an html format while allowing
   * the response to still return with the correct mime-type.
   *
   * The advantage is that the normal templates (with a format-specific
   * filename suffix) will be used. This is useful in sympal_content_renderer
   * when we simply want to get ourselves to the template so we can
   * echo the content renderer.
   *
   * @return void
   */
  public function fakeHtmlRequest()
  {
    $request = $this->_actions->getRequest();
    $response = $this->_actions->getResponse();

    sfConfig::set('sf_web_debug', false);

    $format = $request->getRequestFormat();
    $request->setRequestFormat('html');
    $this->_actions->setLayout(false);

    if ($mimeType = $request->getMimeType($format))
    {
      $response->setContentType($mimeType);
    }
  }

  /**
   * Loads the metadata from the content object
   *
   * @return void
   */
  protected function _loadMetaData()
  {
    $response = $this->_actions->getResponse();

    // set the page title or try to generate it automatically
    if ($pageTitle = $this->getContent()->getPageTitleForRendering())
    {
      $response->setTitle($pageTitle);
    }
    else if (sfSympalConfig::get('auto_seo', 'title'))
    {
      if (method_exists($this->getContent()->getContentTypeClassName(), 'getAutoSeoTitle'))
      {
        $pattern = $this->getContent()->Record->getAutoSeoTitle();
      }
      else
      {
        $pattern = sfSympalConfig::get('auto_seo', 'title_format');
      }

      $title = $this->_replaceConstants($this->getContent(), $pattern);
      $response->setTitle($title);
    }

    // meta keywords
    if ($metaKeywords = $this->getContent()->getMetaKeywordsForRendering())
    {
      $response->addMeta('keywords', $metaKeywords);
    }

    // meta description
    if ($metaDescription = $this->getContent()->getMetaDescriptionForRendering())
    {
      $response->addMeta('description', $metaDescription);
    }
  }

  /**
   * Replaces a string full of wildcards with values from the current
   * sfSympalContent object.
   *
   * @static
   * @param ioPage $page
   * @param  string $str The string containing the wildcards (e.g. %title%)
   * @return string
   */
  protected function _replaceConstants($str)
  {
    $replacer = new sfSympalConstantReplacer();

    $replacer->content = $this->getContent();

    return preg_replace_callback(
      '/%(.+?)%/',
      array($replacer, 'replaceConstantsCallback'),
      $str
    );
  }

  /**
   * Handles the situation where a content record is unpublished
   *
   * @return void
   */
  protected function _handleIsPublished()
  {
    //if (!$record->getIsPublished() && !$this->_user->isEditMode())
    if (!$this->getContent()->getIsPublished() && !$this->_getSympalConfiguration()->isEditMode())
    {
      if (sfSympalConfig::get('unpublished_content', 'forward_404'))
      {
        $this->_actions->forward404('Content has not been published yet!');
      }
      else if ($forwardTo = sfSympalConfig::get('unpublished_content', 'forward_to'))
      {
        $this->_actions->forward($forwardTo[0], $forwardTo[1]);
      }
    }
  }

  /**
   * @return sfSympalPluginConfiguration
   */
  protected function _getSympalConfiguration()
  {
    return $this->_actions
      ->getContext()
      ->getConfiguration()
      ->getPluginConfiguration('sfSympalPlugin');
  }
}