<?php

/**
 * Default actions class for sympal.
 * 
 * Similar to symfony's "default" module, but with additional actions
 * 
 * @package     
 * @subpackage  
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
   * Changes the user's culture
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
   * Changes the user's edit culture
   */
  public function executeChange_edit_language(sfWebRequest $request)
  {
    $this->getUser()->setEditCulture($request->getParameter('language'));
    return $this->redirect($this->getRequest()->getReferer($this->getUser()->getReferer('@homepage')));
  }
}
