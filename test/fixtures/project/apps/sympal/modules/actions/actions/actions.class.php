<?php

class actionsActions extends sfActions
{
  public function executeClearCache()
  {
    $this->clearCache();

    return sfView::NONE;
  }

  public function executeGetSympalConfiguration()
  {
    $this->renderText(get_class($this->getSympalConfiguration()));

    return sfView::NONE;
  }
}