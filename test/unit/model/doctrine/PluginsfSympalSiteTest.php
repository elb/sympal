<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(5);

$t->info('1 - Test that the delete recurses on the application level to the sfSympalContent records');
$site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug('sympal');
$products = Doctrine_Core::getTable('Product')->createQuery()->execute();

$t->isnt(count($site->Content), 0, 'Sanity check: the sfSympalSite record has at least one Content record');
$t->isnt(count($products), 0, 'Sanity check: We begin with more than 0 Product objects');

$site->delete();

$site->refreshRelated('Content');
$products = Doctrine_Core::getTable('Product')->createQuery()->execute();
$t->is(count($site->Content), 0, 'The site now has no content. This would happen with or without the application-level delete recursion');
$t->is(count($products), 0, 'All of the Product records are gone due to the application-level delete recursion onto sfSympalContent');

$t->info('2 - Quick check on ->deleteApplication');
$site->deleteApplication();
$t->is(file_exists(sfConfig::get('sf_app_dir')), false, 'The application directory no longer exists');