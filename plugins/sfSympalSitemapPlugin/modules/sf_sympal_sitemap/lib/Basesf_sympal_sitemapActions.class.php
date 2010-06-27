<?php

class Basesf_sympal_sitemapActions extends sf_sympal_sitemapActions
{
  /**
   * Renders the sitemap
   */
  public function executeSitemap(sfWebRequest $request)
  {
    $this->setLayout(false);
    $this->sitemapGenerator = new sfSympalSitemapGenerator($this->getContext()->getConfiguration()->getApplication());
  }
}