<?php

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';
require_once sfConfig::get('sf_lib_dir').'/test/unitHelper.php';

$t = new lime_test(2);

$t->info('1 - Test that there is a fallback culture for the doctrine record.');

$t->info('  1.1 - Create a record with data on the en culture, the default culture');
$product = new Product();
$product['Translation']['en']['name'] = 'english name';
$product['Translation']['en']['description'] = 'english description';

$t->info('  1.2 - Set the name on the spanish culture and switch to the spanish culture.');
$product['Translation']['es']['name'] = 'nombre';

$context->getUser()->setCulture('es');

$t->is($product->name, 'nombre', 'The name returns the spanish name as expected (we\'re in the spanish culture)');
$t->is($product->description, 'english description', 'The description falls back to the english description.');