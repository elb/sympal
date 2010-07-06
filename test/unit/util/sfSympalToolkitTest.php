<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$t = new lime_test(17);

$t->info('Make a call to the test/test partial. It prints out the value of $var');
$resource = sfSympalToolkit::getSymfonyResource('test', 'test', array('var' => 'Unit Testing'));
$t->is($resource, 'Unit Testing', '::getSymfonyResource() called the correct partial, returned the correct value');

$t->is(sfSympalToolkit::getDefaultApplication(), 'sympal', '::getDefaultApplication() return "sympal"');

$t->info('1 - Test the getContentRoutesYaml() method');
  $type = create_content_type($t, 'Product');
  $type->default_path = '/products/:slug';
  $type->save();

  $content = sfSympalContent::createNew('Product');
  $content->name = 'testing product';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 1, 'One route is created for the one content type');
  test_content_route($t, $routes['product'], $type);

  $t->info('  1.1 - Pass a custom module');
  $content->module = 'custom_module';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 2, 'Two routes are created for the one content type and one custom content');
  test_content_route($t, $routes['sympal_content_testing_product'], $type, '/products/testing-product', array('module' => 'custom_module', 'sympal_content_id' => $content->id));

  $t->info('  1.2 - Pass a custom action');
  $content->module = null;
  $content->action = 'custom_action';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 2, 'Two routes are created for the one content type and one custom content');
  test_content_route($t, $routes['sympal_content_testing_product'], $type, '/products/testing-product', array('action' => 'custom_action', 'sympal_content_id' => $content->id));

  $t->info('  1.3 - Pass a custom_path');
  $content->action = null;
  $content->custom_path = '/my/path';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 2, 'Two routes are created for the one content type and one custom content');
  test_content_route($t, $routes['sympal_content_testing_product'], $type, '/my/path.:sf_format', array('sympal_content_id' => $content->id));

  $t->info('  1.4 - Pass a custom_path that is the homepage');
  $content->action = null;
  $content->custom_path = '/';
  $content->save();
  $content->getContentRouteObject()->compile($content);
  $routes = sfYaml::load(sfSympalToolkit::getContentRoutesYaml());
  $t->is(count($routes), 2, 'Two routes are created for the one content type and one custom content');
  test_content_route($t, $routes['homepage'], $type, '/', array('sympal_content_id' => $content->id));

function test_content_route(lime_test $t, $route, sfSympalContentType $type, $url = '/products/:slug.:sf_format', $params = array())
{ 
  $params = array_merge(array(
    'module'                  => 'sympal_content_renderer',
    'action'                  => 'index',
    'sf_format'               => 'html',
    'sympal_content_type'     => 'Product',
    'sympal_content_type_id'  => $type->id,
    'sympal_content_id'       => null,
  ), $params);

  $t->is($route['url'], $url, 'The route url is correct.');
  $t->is($route['param'], $params, 'The routes param are correct');
}