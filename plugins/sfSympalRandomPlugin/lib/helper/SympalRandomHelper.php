<?php

/**
 * Returns the url to a gravatar image based on the given email address
 *
 * @param string $emailAddress The email address to lookup in gravatar
 * @param string The size of the image to return
 */
function get_gravatar_url($emailAddress, $size = 40)
{
  $default = sfSympalConfig::get('gravatar_default_image');
  $default = image_path($default, true);

  $url = 'http://www.gravatar.com/avatar.php?gravatar_id='.md5(strtolower($emailAddress)).'&default='.urlencode($default).'&size='.$size;

  return $url;
}


/**
 * Get the Sympal flash boxes
 *
 * @return string $html
 */
function get_sympal_flash()
{
  return get_partial('sympal_default/flash');
}

/**
 * Get a sfSympalMenuBreadcrumbs instances for the given MenuItem
 *
 * @param MenuItem $menuItem  The MenuItem instance to generate the breadcrumbs for
 * @param string $subItem     A string to append to the end of the breadcrumbs
 * @return string $html
 */
function get_sympal_breadcrumbs($menuItem, $subItem = null)
{
  if (!$menuItem)
  {
    return false;
  }

  // If we were passed an array then generate manual breacrumbs from it
  if (is_array($menuItem))
  {
    $breadcrumbs = sfSympalMenuBreadcrumbs::generate($menuItem);
  } else {
    $breadcrumbs = $menuItem->getBreadcrumbs($subItem);
  }

  if ($html = (string) $breadcrumbs)
  {
    return $html;
  } else {
    return false;
  }
}

/**
 * Shortcut helper method to use jquery in your code
 *
 * @param array $plugins Optional array of jQuery plugins to load
 * @return void
 */
function sympal_use_jquery($plugins = array())
{
  sympal_use_javascript('jquery.js');
}
/**
 * Helper method for using a Sympal javascript file.
 *
 * @param string $path
 * @param string $position
 * @return void
 */
function sympal_use_javascript($path, $position = 'last')
{
  return use_javascript(sfSympalConfig::getAssetPath($path), $position);
}

/**
 * Helper method for using a Sympal stylesheet file
 *
 * @param string $path
 * @param string $position
 * @return void
 */
function sympal_use_stylesheet($path, $position = 'last')
{
  return use_stylesheet(sfSympalConfig::getAssetPath($path), $position);
}