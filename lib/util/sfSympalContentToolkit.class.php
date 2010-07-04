<?php

class sfSympalContentToolkit
{

  /**
   * Get the content routes yaml
   *
   * @return string $yaml
   */
  public static function getContentRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/content_routes.cache.yml';
    if (file_exists($cachePath) && sfConfig::get('sf_environment') !== 'test')
    {
      return file_get_contents($cachePath);
    }

    try {
      $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    sf_format: html
    sympal_content_type: %s
    sympal_content_type_id: %s
    sympal_content_id: %s
  class: sfDoctrineRoute
  options:
    model: sfSympalContent
    type: object
    method: getContent
    allow_empty: true
  requirements:
    sf_culture:  (%s)
    sf_format:   (%s)
    sf_method:   [post, get]
';

      $routes = array();
      $siteSlug = sfSympalConfig::getCurrentSiteName();
      
      if (!sfContext::hasInstance())
      {
        $configuration = ProjectConfiguration::getApplicationConfiguration(sfConfig::get('sf_app'), 'prod', false);
        sfContext::createInstance($configuration);
      }

      /*
       * Step 1) Process all sfSympalContent records with a custom_path,
       *         module, or action. These have sympal_content_* routes
       */
      $contents = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->innerJoin('c.Site s')
        ->where("(c.custom_path IS NOT NULL AND c.custom_path != '') OR (c.module IS NOT NULL AND c.module != '') OR (c.action IS NOT NULL AND c.action != '')")
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();
      foreach ($contents as $content)
      {
        $routes['content_'.$content->getId()] = sprintf($routeTemplate,
          substr($content->getRouteName(), 1),
          $content->getRoutePath(),
          $content->getModuleToRenderWith(),
          $content->getActionToRenderWith(),
          $content->Type->name,
          $content->Type->id,
          $content->id,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      /*
       * Step 2) Create a route for each sfSympalContentType record
       */
      $contentTypes = Doctrine::getTable('sfSympalContentType')
        ->createQuery('t')
        ->execute();
      foreach ($contentTypes as $contentType)
      {
        $routes['content_type_'.$contentType->getId()] = sprintf($routeTemplate,
          substr($contentType->getRouteName(), 1),
          $contentType->getRoutePath(),
          $contentType->getModuleToRenderWith(),
          $contentType->getActionToRenderWith(),
          $contentType->name,
          $contentType->id,
          null,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);

      return $routes;
    }
    catch (Exception $e)
    {
      // for now, I'd like to not obfuscate the errors - rather reportthem
      throw $e;
    }
  }
}