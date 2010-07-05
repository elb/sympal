<?php

/**
 * Default actions class for sympal.
 * 
 * @package     sfSympalPlugin
 * @subpackage  actions
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-02
 * @version     svn:$Id$ $Author$
 */
class Basesympal_defaultActions extends sfActions
{
  /**
   * The default action for handling unpublished content
   */
  public function executeUnpublished_content(sfWebRequest $request)
  {
  }

  /**
   * Changes the user's culture and then redirects
   */
  public function executeChange_language(sfWebRequest $request)
  {
    $oldCulture = $this->getUser()->getCulture();
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::getLanguageCodes()));
    unset($this->form[$this->form->getCSRFFieldName()]);

    $this->form->process($request);

    $newCulture = $this->getUser()->getCulture();

    $this->getUser()->setFlash('notice', 'Changed language successfully!');

    return $this->redirect(str_replace('/'.$oldCulture.'/', '/'.$newCulture.'/', $this->getRequest()->getReferer($this->getUser()->getReferer('@homepage'))));
  }

  /**
   * Changes the user's edit culture and then redirects
   */
  public function executeChange_edit_language(sfWebRequest $request)
  {
    $this->getUser()->setEditCulture($request->getParameter('language'));
    return $this->redirect($this->getRequest()->getReferer($this->getUser()->getReferer('@homepage')));
  }

  /**
   * User is forwarded to this action when a site record exists but not
   * content for that site exists yet
   */
  public function executeNew_site(sfWebRequest $request)
  {
  }
}
