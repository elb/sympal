<?php

class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;

  protected
    $_site,
    $_siteSlug,
    $_sympalConfiguration,
    $_symfonyContext,
    $_currentMenuItem,
    $_currentContent,
    $_currentSite,
    $_previousTheme,
    $_theme,
    $_themeObjects = array();

  public function __construct(sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_siteSlug = $symfonyContext->getConfiguration()->getApplication();
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
  }

  public function getCurrentMenuItem()
  {
    return $this->_currentMenuItem;
  }

  public function setCurrentMenuItem(sfSympalMenuItem $menuItem)
  {
    $this->_currentMenuItem = $menuItem;
  }

  public function getCurrentContent()
  {
    return $this->_currentContent;
  }

  public function setCurrentContent(sfSympalContent $content)
  {
    $this->_currentContent = $content;
    if (!$this->_site)
    {
      $this->_site = $content->getSite();
    }
  }

  public function setSite(sfSympalSite $site)
  {
    $this->_site = $site;
  }

  public function getSite()
  {
    if (!$this->_site)
    {
      $this->_site =  Doctrine_Core::getTable('sfSympalSite')
        ->createQuery('s')
        ->where('s.slug = ?', $this->_siteSlug)
        ->fetchOne();
    }
    return $this->_site;
  }

  public function getSiteSlug()
  {
    return $this->_siteSlug;
  }

  public function getTheme()
  {
    return $this->_theme;
  }

  public function getThemeObject($name = null)
  {
    $theme = $name ? $name : $this->_theme;
    if (!isset($this->_themeObjects[$theme]))
    {
      $configurationArray = sfSympalConfig::get('themes', $theme);
      $configurationArray['name'] = $theme;

      $configurationClass = isset($configurationArray['config_class']) ? $configurationArray['class'] : 'sfSympalThemeConfiguration';
      $themeClass = isset($configurationArray['theme_class']) ? $configurationArray['theme_class'] : 'sfSympalTheme';
      $configuration = new $configurationClass($configurationArray);
      $this->_themeObjects[$theme] = new $themeClass($this, $configuration);
    }
    return isset($this->_themeObjects[$theme]) ? $this->_themeObjects[$theme] : false;
  }

  public function getThemeObjects()
  {
    return $this->_themeObjects;
  }

  public function getPreviousTheme()
  {
    return $this->getThemeObject($this->_previousTheme);
  }

  public function unloadPreviousTheme()
  {
    if ($previousTheme = $this->getPreviousTheme())
    {
      $previousTheme->unload();
    }
  }

  public function setTheme($theme)
  {
    $this->_theme = $theme;
  }

  public function loadTheme($name = null)
  {
    $this->_previousTheme = $this->_theme;
    $theme = $name ? $name : $this->_theme;
    $this->setTheme($theme);
    if ($theme = $this->getThemeObject($theme))
    {
      return $theme->load();
    }
  }

  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  public function getSymfonyContext()
  {
    return $this->_symfonyContext;
  }

  public function getContentRenderer(sfSympalContent $content, $format = null)
  {
    return new sfSympalContentRenderer($this, $content, $format);
  }

  public static function getInstance($site = null)
  {
    if (is_null($site))
    {
      return self::$_current;
    }

    if (!isset(self::$_instances[$site]))
    {
      throw new sfException($site.' instance does not exist.');
    }
    return self::$_instances[$site];
  }

  public static function createInstance(sfContext $symfonyContext)
  {
    $site = $symfonyContext->getConfiguration()->getApplication();
    $sympalConfiguration = $symfonyContext->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();

    $instance = new self($sympalConfiguration, $symfonyContext);
    self::$_instances[$site] = $instance;
    self::$_current = $instance;

    return self::$_instances[$site];
  }
}