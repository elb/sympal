<?php

/**
 * Represents a site.
 */
abstract class PluginsfSympalSite extends BasesfSympalSite
{
  /**
   * Delete site record and all associated content.
   *
   * @return boolean
   */
  public function delete(Doctrine_Connection $conn = null)
  {
    // we *need* to call sfSympalContent::delete() for each record
    foreach($this->Content as $record)
    {
      $record->delete();
    }

    return parent::delete();
  }

  /**
   * Delete application associated with site.
   *
   * @return null
   */
  public function deleteApplication()
  {
    sfSympalToolkit::deleteApplication($this->slug);
  }

  /**
   * Getter and setter for backwards-compatibility with field name change
   * from title to name
   */
  public function setTitle($v)
  {
    $this['name'] = $v;
  }
  public function getTitle()
  {
    return $this['name'];
  }
}