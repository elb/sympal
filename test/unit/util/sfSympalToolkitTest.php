<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$t = new lime_test(22);

$t->info('1 - Make a call to the product/test partial. It prints out the value of $var');
  $resource = sfSympalToolkit::getSymfonyResource('product', 'test', array('var' => 'Unit Testing'));
  $t->is($resource, 'Unit Testing', '::getSymfonyResource() called the correct partial, returned the correct value');

  $t->is(sfSympalToolkit::getDefaultApplication(), 'sympal', '::getDefaultApplication() return "sympal"');

$t->info('2 - Test the getContentRoutesYaml() method');

  $product = new Product();
  $product->name = 'testing product';
  $product->save();
  $content = $product->Content;
  
  $type = Doctrine_Core::getTable('sfSympalContentType')->findOneByTypeKey('product');
  
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 2, 'Two routes are created for the two content types');
  test_content_route($t, $routes['product'], $type);

  $t->info('  1.1 - Pass a custom module');
  $content->rendering_method = 'custom_module';
  $content->save();
  $routeName = 'sympal_content_'.$content->id;
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 3, 'Three routes are created for the two content types and one custom content');
  test_content_route($t, $routes[$routeName], $type, '/product/testing-product', array('module' => 'product', 'content_id' => $content->id));

  $t->info('  1.2 - Pass a custom action');
  $content->rendering_method = 'custom_action';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 3, 'Three routes are created for the two content types and one custom content');
  test_content_route($t, $routes[$routeName], $type, '/product/testing-product', array('action' => 'customAction', 'content_id' => $content->id));

  $t->info('  1.3 - Pass a custom_path');
  $content->rendering_method = null;
  $content->custom_path = '/my/path';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 3, 'Three routes are created for the two content types and one custom content');
  test_content_route($t, $routes[$routeName], $type, '/my/path.:sf_format', array('content_id' => $content->id));

  $t->info('  1.4 - Pass a custom_path that is the homepage');
  $content->custom_path = '/';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 3, 'Three routes are created for the two content types and one custom content');
  test_content_route($t, $routes['homepage'], $type, '/', array('content_id' => $content->id));

function test_content_route(lime_test $t, $route, sfSympalContentType $type, $url = '/product/:slug.:sf_format', $params = array())
{ 
  $params = array_merge(array(
    'module'                  => 'sympal_content_renderer',
    'action'                  => 'index',
    'sf_format'               => 'html',
    'content_id'              => null,
  ), $params);

  $t->is($route['url'], $url, 'The route url is correct.');
  $t->is($route['param'], $params, 'The routes param are correct');
  $t->is($route['options']['model'], 'Product', 'The model is set correctly');
}
