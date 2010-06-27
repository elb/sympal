<?php

class Basesympal_random_default extends sympal_random_defaultActions
{
  /**
   * Requests are forwarded here when using the ->askConfirmation()
   * method on the actions class
   */
  public function executeAsk_confirmation(sfWebRequest $request)
  {
    if ($request->isXmlHttpRequest())
    {
      $this->setLayout(false);
    }
    else
    {
      $this->loadAdminTheme();
    }

    $this->url = $request->getUri();
    $this->title = $request->getAttribute('title');
    $this->message = $request->getAttribute('message');
    $this->isAjax = $request->getAttribute('is_ajax');
  }

  /**
   * User is forwarded to this action when a site record exists but not
   * content for that site exists yet
   */
  public function executeNew_site(sfWebRequest $request)
  {
    $this->loadSiteTheme();
  }
}