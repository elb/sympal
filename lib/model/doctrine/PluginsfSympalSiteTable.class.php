<?php
/**
 * Table class for sfSympalSite
 * 
 * @package     sfSympalPlugin
 * @subpackage  Doctrine_Table
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class PluginsfSympalSiteTable extends Doctrine_Table
{
  /**
   * Fetches the current sfSympalSite record
   *
   * @param bool $create Whether or not to create the record of it doesn't exist
   * @return sfSympalSite|null
   */
  public function fetchCurrent($create = false)
  {
    return $this->fetchSite(sfSympalConfig::getCurrentSiteName(), $create);
  }

  /**
   * @param  string $siteName The name of the site to get or create
   * @param  bool   $create   Whether or not to create the record of it doesn't exist
   * @return sfSympalSite|null
   */
  public function fetchSite($siteName, $create = false)
  {
    $site = $this->createQuery('s')
      ->where('s.name = ?', $siteName)
      ->fetchOne();

    if (!$site && $create)
    {
      $site = new sfSympalSite();
      $site->name = $siteName;
      $site->slug = $siteName;
      $site->save();
    }

    return $site;
  }
}