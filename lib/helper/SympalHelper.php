<?php

/**
 * Link to another Sympal site
 *
 * @param string $site 
 * @param string $name 
 * @param string $path 
 * @return string $html
 */
function sympal_link_to_site($site, $name, $path = null)
{
  $request = sfContext::getInstance()->getRequest();
  $env = sfConfig::get('sf_environment');
  $file = $env == 'dev' ? $site.'_dev.php' : ($site.'.php');
  return '<a href="'.$request->getRelativeUrlRoot().'/'.$file.($path ? '/'.$path:null).'">'.$name.'</a>';
}
