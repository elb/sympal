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
    $siteName = sfSympalConfig::getCurrentSiteName();

    $site = $this->createQuery('s')
      ->where('s.name = ?', $siteName)
      ->fetchOne();

    if (!$site && $create)
    {
      $site = new sfSympalSite();
      $site->name = $siteName;
      $site->save();
    }

    return $site;
  }
}