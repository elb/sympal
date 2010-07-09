<?php

/**
 * Extension of sfConfig to allow easy access to configuration values
 * under the "sympal_config" key as well as other helper methods.
 *
 * @package     sfSympalPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfSympalConfig extends sfConfig
{
  /**
   * Return a configuration value under the app.yml "sympal_config" key
   *
   * @example
   * all:
   *   sympal_config:
   *     foo:        bar
   *     deeper:
   *       foo_deep: bar_deep
   *
   * sfSympalConfig::get('foo')  // returns bar
   * sfSympalConfig::get('deeper', 'foo_deep') // return bar_deep
   *
   * @param string $group   The top-level config key after "sympal_config"
   * @param string $name    The next-level config key after $group (or null)
   * @param mixed  $default The default value to return if the config doesn't exist
   * 
   * @return mixed $value
   */
  public static function get($group, $name = null, $default = null)
  {
    $default = $default === null ? false : $default;
    if ($name === null)
    {
      return isset(self::$config['app_sympal_config_'.$group]) ? self::$config['app_sympal_config_'.$group] : $default;
    }
    else
    {
      return isset(self::$config['app_sympal_config_'.$group][$name]) ? self::$config['app_sympal_config_'.$group][$name] : $default;
    }
  }

  /**
   * Set a setting value
   *
   * @param string $group The group name to put the value on
   * @param string $name  Either the name of the config inside the group
   *                      or the value if the setting is not in a group
   * @param string $value If the setting is inside a group, this is the value
   *                      to set it to. Otherwise this is null.
   * @return void
   */
  public static function set($group, $name, $value = null)
  {
    if ($value === null)
    {
      self::$config['app_sympal_config_'.$group] = $name;
    }
    else
    {
      self::$config['app_sympal_config_'.$group][$name] = $value;
    }
  }

  /**
   * Helper to return very deep configuration values
   *
   * all:
   *   sympal_config:
   *     group:
   *       subGroup:
   *         name:      value
   *
   * @param string $group The group name for the config
   * @param string $subGroup The sub group name for the config
   * @param string $name The name of the config key
   * @param mixed $default The default value to return if the config doesn't exist
   */
  public static function getDeep($group, $subgroup, $name, $default = null)
  {
    $data = sfSympalConfig::get($group, $subgroup);

    if (!$data || !is_array($data))
    {
      return $default;
    }

    return isset($data[$name]) ? $data[$name] : $default;
  }

  /**
   * Returns the "name" of the current site
   *
   * @throws sfException
   * @return string
   */
  public static function getCurrentSiteName()
  {
    if (!$site = sfConfig::get('sf_sympal_site', sfConfig::get('sf_app')))
    {
      throw new sfException('No current site defined. If you receive this error in a task, be sure to initialize an application configuration.');
    }

    return $site;
  }

  /**
   * Get the array of language codes for i18n
   *
   * @return array $languageCodes
   */
  public static function getLanguageCodes()
  {
    return self::get('language_codes', null, array());
  }

  /**
   * Check if i18n is enabled globally or for a given model
   *
   * @param string $name Optional name of the model to check for i18n on
   * @return boolean
   */
  public static function isI18nEnabled($name = null)
  {
    if ($name)
    {
      if (is_object($name))
      {
        $name = get_class($name);
      }

      $ret = sfConfig::get('sf_i18n') && self::get('internationalized_models', $name);
    }
    else
    {
      $ret = sfConfig::get('sf_i18n');
    }

    $languageCodes = self::getLanguageCodes();
    if ($ret && empty($languageCodes))
    {
      throw new sfException('I18n is enabled, but no language codes have been defined in app.yml');
    }

    return $ret;
  }

  /**
   * Get the current installed version of sympal
   *
   * @return string $version
   */
  public static function getCurrentVersion()
  {
    return sfSympalPluginConfiguration::VERSION;
  }

  /**
   * Get array of configured content templates for a given model name
   *
   * @param string $model
   * @return array $contentTemplates
   */
  public static function getContentTemplates($model)
  {
    return sfSympalConfig::getDeep('content_types', $model, 'content_templates', array());
  }

  /**
   * Write a setting to the config/app.yml. The api of this is the same as set()
   *
   * @see sfSympalConfig::set()
   * @param string $group 
   * @param string $name 
   * @param string $value 
   * @param string $application Whether or not to write this setting to the app config file
   * @return void
   */
  public static function writeSetting($group, $name, $value = null, $application = false)
  {
    if ($application)
    {
      $path = sfConfig::get('sf_app_dir').'/config/app.yml';
    }
    else
    {
      $path = sfConfig::get('sf_config_dir').'/app.yml';
    }

    if (!file_exists($path))
    {
      touch($path);
    }
    $array = (array) sfYaml::load(file_get_contents($path));

    if ($value === null)
    {
      $array['all']['sympal_config'][$group] = $name;
    }
    else
    {
      $array['all']['sympal_config'][$group][$name] = $value;
    }

    sfSympalConfig::set($group, $name, $value);
    file_put_contents($path, sfYaml::dump($array, 4));
  }

  /**
   * Get a overridden asset path or return the original asset path
   *
   * @param string $path
   * @return string $path
   */
  public static function getAssetPath($path)
  {
    return isset(self::$config['app_sympal_config_asset_paths'][$path]) ? self::$config['app_sympal_config_asset_paths'][$path] : $path;
  }

  /**
   * Get name of the admin generator theme to use
   *
   * @return string $theme
   */
  public static function getAdminGeneratorTheme()
  {
    return sfSympalConfig::get('default_admin_generator_theme', null, 'default');
  }

  /**
   * Get the name of the admin generator class to use
   *
   * @return string $class
   */
  public static function getAdminGeneratorClass()
  {
    return sfSympalConfig::get('default_admin_generator_class', null, 'sfDoctrineGenerator');
  }

  /**
   * Check whether a Doctrine query result cache key should use result cache or not
   *
   * @param string $key
   * @return boolean
   */
  public static function shouldUseResultCache($key)
  {
    if (isset(self::$config['app_sympal_config_orm_cache']['queries'][$key]['enabled'])
      && self::$config['app_sympal_config_orm_cache']['queries'][$key]['enabled']
      && isset(self::$config['app_sympal_config_orm_cache']['result'])
      && self::$config['app_sympal_config_orm_cache']['result']
    )
    {
      return isset(self::$config['app_sympal_config_orm_cache']['queries'][$key]['lifetime']) ? self::$config['app_sympal_config_orm_cache']['queries'][$key]['lifetime'] : self::$config['app_sympal_config_orm_cache']['lifetime'];
    }
    else
    {
      return false;
    }
  }

  /**
   * Attempts to look through all of the content_types to find one that
   * matches the given model. This is used when a content type model is
   * trying to guess its content type.
   *
   * If there are multiple content types configured for a given model,
   * this will use the first it finds. In those cases, the ContentType
   * should be manually set so the record doesn't have to guess.
   *
   * @static
   * @throws sfException
   * @param  string $model The model to match up to a content type
   * @return string
   */
  public static function getContentTypeKeyFromModel($model)
  {
    foreach (self::get('content_types', null, array()) as $key => $typeArr)
    {
      if (isset($typeArr) && $typeArr['model'] == $model)
      {
        return $key;
      }
    }

    throw new sfException(sprintf('Could not find content type for model "%s"', $model));
  }
}