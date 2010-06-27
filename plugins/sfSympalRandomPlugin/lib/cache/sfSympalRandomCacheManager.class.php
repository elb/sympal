<?php

class sfSympalRandomCacheManager
{
  /**
   * Get the Doctrine cache driver to use for Doctrine query and result cache
   *
   * @return Doctrine_Cache_Driver $driver
   */
  public static function getOrmCacheDriver()
  {
    if (extension_loaded('apc'))
    {
      // set the prefix to something that will be different between projects
      $prefix = 'doctrine_'.md5(sfConfig::get('sf_root_dir'));

      return new Doctrine_Cache_Apc(array('prefix' => $prefix));
    }
    else
    {
      return new Doctrine_Cache_Array();
    }
  }
}