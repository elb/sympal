<?php

class sfSympalRedirectToolkit
{
  /**
   * Get the redirect routes yaml for the routing.yml
   *
   * @return string $yaml
   */
  public static function getRedirectRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/redirect_routes.cache.yml';
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
    id: %s
';

      $routes = array();
      $siteSlug = sfConfig::get('sf_app');

      $redirects = Doctrine::getTable('sfSympalRedirect')
        ->createQuery('r')
        ->innerJoin('r.Site s')
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($redirects as $redirect)
      {
        $routes[] = sprintf($routeTemplate,
          'sympal_redirect_'.$redirect->getId(),
          $redirect->getSource(),
          'sympal_redirecter',
          'index',
          $redirect->getId()
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);
      return $routes;
    } catch (Exception $e) { }
  }
}