<?php

class sfSympalRandomPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    // Connect to the sympal.load_admin_menu event
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'setupAdminMenu'));

    // Connect with theme.filter_asset_paths to rewrite asset paths from theme
    $this->dispatcher->connect('theme.filter_asset_paths', array($this, 'filterThemeAssetPaths'));

    $this->dispatcher->connect('sympal.load', array($this, 'configureSympal'));

    $this->_configureDoctrine();
  }

  public function configureSympal(sfEvent $event)
  {
    // extend the component/action class
    $actions = new sfSympalRandomActions();
    $actions->setSympalContext($this);

    $this->_dispatcher->connect('component.method_not_found', array($actions, 'extend'));
  }

  /**
   * Listens to the sympal.load_admin_menu to configure the admin menu
   */
  public function setupAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    // Setup the Content menu
    $manageContent = $menu->getChild('content');
    $manageContent->setLabel('Content');

    $manageContent->addChild('Search', '@sympal_admin_search');

    $contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
    foreach ($contentTypes as $contentType)
    {
      $manageContent
        ->addChild($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getId())
        ->setCredentials(array('ManageContent'));
    }

    $manageContent
      ->addChild('Slots', '@sympal_content_slots')
      ->setCredentials(array('ManageSlots'));

    $manageContent
      ->addChild('XML Sitemap', '@sympal_sitemap')
      ->setCredentials(array('ViewXmlSitemap'));


    // Setup the Site Administration menu
    $siteAdministration = $menu->getChild('site_administration');
    $siteAdministration->setLabel('Site Administration');

    $siteAdministration
      ->addChild('404 Redirects', '@sympal_redirects')
      ->setCredentials(array('ManageRedirects'));

    $siteAdministration
      ->addChild('Edit Site', '@sympal_sites_edit?id='.sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId())
      ->setCredentials(array('ManageSites'));


    // Add to the Administration menu
    $administration = $menu->getChild('administration');

    $administration->addChild('Content Types', '@sympal_content_types')
      ->setCredentials(array('ManageContentTypes'));

    $administration->addChild('Sites', '@sympal_sites')
      ->setCredentials(array('ManageSites'));


    // Add a Content menu if applicable
    $content = $this->_sympalContext->getService('site_manager')->getCurrentContent();
    if ($content)
    {
      $contentEditor = $menu->getChild($content->getType()->slug);
      $contentEditor->setLabel(sprintf(__('%s Actions'), $content->getType()->getLabel()));

      // If in the admin, put a link to view the content
      if (sfSympalConfiguration::getActive()->isAdminModule())
      {
        $contentEditor
          ->addChild(sprintf(__('View %s'), $content->getType()->getLabel()), $content->getRoute());
      }

      $contentEditor
        ->addChild(sprintf(__('Create New %s'), $content->getType()->getLabel()), '@sympal_content_create_type?type='.$content['Type']['slug'])
        ->setCredentials('ManageContent');

      $contentEditor
        ->addChild(sprintf(__('Edit %s'), $content->getType()->getLabel()), $content->getEditRoute())
        ->setCredentials('ManageContent');

      $contentEditor
        ->addChild(__('Edit Content Type'), '@sympal_content_types_edit?id='.$content->getType()->getId())
        ->setCredentials('ManageMenus');

      // Add a menu item entry
      $menuItem = $this->_sympalContext->getService('menu_manager')->getCurrentMenuItem();
      if ($menuItem && $menuItem->exists())
      {
        $contentEditor
          ->addChild(__('Edit Menu Item'), '@sympal_content_menu_item?id='.$content->getId())
          ->setCredentials('ManageMenus');
      }
      else
      {
        $contentEditor
          ->addChild(__('Add to Menu'), '@sympal_content_menu_item?id='.$content->getId())
          ->setCredentials('ManageMenus');
      }

      // Add publish/unpublish icons
      $user = sfContext::getInstance()->getUser();
      if($user->hasCredential('PublishContent'))
      {
        if($content->getIsPublished())
        {
          $contentEditor
            ->addChild(__('Unpublish'), '@sympal_unpublish_content?id='.$content->id, 'title='.__('Published on %date%', array('%date%' => format_date($content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.'));
        }
        elseif($content->getIsPublishInTheFuture())
        {
          $contentEditor
            ->addChild(__('Unpublish'), '@sympal_unpublish_content?id='.$content->id, 'title='.__('Will publish on %date%', array('%date%' => format_date($content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.'));
        }
        else
        {
          $contentEditor
            ->addChild(__('Publish'), '@sympal_publish_content?id='.$content->id, 'title='.__('Has not been published yet. '.__('Click to publish content.')));
        }
      }

      if (sfSympalConfig::isI18nEnabled())
      {
        foreach (sfSympalConfig::getLanguageCodes() as $code)
        {
          if (sfContext::getInstance()->getUser()->getEditCulture() != $code)
          {
            $contentEditor->addChild(sprintf(__('Edit in %s'), format_language($code)), '@sympal_change_edit_language?language='.$code, 'title='.sprintf(__('Edit %s version'), format_language($code)));
          }
        }
      }
    }
  }

  /**
   * Configure the Doctrine manager for Sympal
   *
   * @return void
   */
  protected function _configureDoctrine()
  {
    if (!class_exists('Doctrine_Manager'))
    {
      return;
    }

    $doctrineManager = Doctrine_Manager::getInstance();

    // new records with existing data aren't overridden when new data is hydraed
    $doctrineManager->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, false);

    if (sfSympalConfig::get('orm_cache', 'enabled', true))
    {
      $driver = sfSympalCacheManager::getOrmCacheDriver();

      $doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $driver);

      if (sfSympalConfig::get('orm_cache', 'result', false))
      {
        $doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $driver);
        $doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, sfSympalConfig::get('orm_cache', 'lifetime', 86400));
      }
    }
  }

  /**
   * Listens to the theme.filter_asset_paths event and rewrites all of
   * the assets from themes before outputting them
   */
  public function filterThemeAssetPaths(sfEvent $event, $assets)
  {
    foreach ($assets as $key => $asset)
    {
      $assets[$key]['file'] = sfSympalConfig::getAssetPath($asset['file']);
    }

    return $assets;
  }
}