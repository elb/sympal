<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$dispatcher = $configuration->getEventDispatcher();
$t = new lime_test(7);

// listener on the cache prime
function cache_primer(sfEvent $event)
{
  $event->getSubject()->set('prime_test', 'unit test');
}
$dispatcher->connect('sympal.cache.prime', 'cache_primer');

$t->info('1 - Create the cache manager and check the cache prime');
  $driver = get_new_driver();
  $manager = new sfSympalCacheManager($dispatcher, $driver);

  $t->is($manager->getCacheDriver()->get('prime_test'), 'unit test', 'The cache is primed via the sympal.cache.prime event');

  $manager->getCacheDriver()->set('prime_test', 'other value');
  $manager->primeCache();
  $t->is($manager->getCacheDriver()->get('prime_test'), 'other value', '->primeCache() does not reprime the cache if not forced');
  $manager->primeCache(true);
  $t->is($manager->getCacheDriver()->get('prime_test'), 'unit test', '->primeCache(true) forces a re-prime of the cache');


$t->info('2 - Test basic cache get and set functionality');
  sfToolkit::clearDirectory('/tmp/sympal');
  $manager = new sfSympalCacheManager(new sfEventDispatcher(), get_new_driver());

  $manager->set('unit_test', 'sympal');
  $t->is($manager->get('unit_test'), 'sympal', '->set() and ->get() work correctly.');
  $t->is($manager->has('unit_test'), true, '->has() returns true for existing cache item.');
  $t->is($manager->has('fake'), false, '->has() returns false for existing cache item.');
  $manager->remove('unit_test');
  $t->is($manager->has('unit_test'), false, '->remove() removes a cache item.');



function get_new_driver()
{
  sfToolkit::clearDirectory('/tmp/sympal');
  return new sfFileCache(array(
    'cache_dir' => '/tmp/sympal',
  ));
}