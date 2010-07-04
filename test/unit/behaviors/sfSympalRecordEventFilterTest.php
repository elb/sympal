<?php

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$t = new lime_test(5);

function listen_method_not_found(sfEvent $event)
{
  sfConfig::set('subject', $event->getSubject());
  sfConfig::set('method', $event['method']);

  if (strpos($event['method'], 'set') === 0)
  {
    sfConfig::set('arguments', $event['arguments']);
  }

  return 'ret-val';
}
$dispatcher = $configuration->getEventDispatcher();
$dispatcher->connect('sympal.product.method_not_found', 'listen_method_not_found');

$t->info('1 - Test the method_not_found event.');

$t->info('  1.1 - Test the setter');
  $product = new Product();
  $product->something_fake = 'value';

  $t->is(get_class(sfConfig::get('subject')), 'Product', 'The subjet is set correctly on a method not found');
  $t->is(sfConfig::get('method'), 'setSomethingFake', 'The method is set correctly on a method not found');
  $t->is(sfConfig::get('arguments'), array('value'), 'The arguments are set correctly on a method not found');

$t->info('  1.1 - Test the setter');
  $product = new Product();
  $ret = $product->something_fake;

  $t->is(get_class(sfConfig::get('subject')), 'Product', 'The subjet is set correctly on a method not found');
  $t->is(sfConfig::get('method'), 'getSomethingFake', 'The method is set correctly on a method not found');