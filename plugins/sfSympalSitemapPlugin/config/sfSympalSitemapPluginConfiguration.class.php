<?php

class sfSympalSitemapPluginConfiguration extends sfPluginConfiguration
{
  public function intialize()
  {
    self::_markClassesAsSafe();
  }

  /**
   * Mark classes safe from the output escaper
   *
   * @return void
   */
  private static function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalSitemapGenerator',
    ));
  }
}