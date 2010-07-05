<?php

class content_action_loaderActions extends sfActions
{
  // bound to a non-object
  public function executeContent(sfWebRequest $request)
  {
    $this->getSympalContentActionLoader()->loadContentRenderer(true);
  }
}