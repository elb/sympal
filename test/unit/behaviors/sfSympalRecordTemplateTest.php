<?php

require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$t = new lime_test(9);

$product = new Product();

$t->info('1 - Test that all the correct filters are set');
  $filters = $product->getTable()->getFilters();
  $validFilters = array(
    'sfSympalDoctrineRecordI18nFilter',
    'sfSympalRecordEventFilter',
    'Doctrine_Record_Filter_Standard',
    'sfSympalContentTypeFilter', // actually comes from sfSympalContenTypeTemplate
  );

  foreach ($filters as $filter)
  {
    $t->ok(in_array(get_class($filter), $validFilters), get_class($filter).' is a filter that should be set');
  }

$t->info('2 - Test that all the correct templates are set');
  $templates = $product->getTable()->getTemplates();
  $validTemplates = array(
    'sfSympalContentTypeTemplate',
    'Doctrine_Template_I18n',
  );

  foreach ($templates as $template)
  {
    $t->ok(in_array(get_class($template), $validTemplates), get_class($template).' is a template that should be set');
  }

$t->info('3 - Test a bunch of methods on the template.');
  $t->is($product->isI18ned(), true, '->isI18ned() returns the correct value');
  $t->is($product->getI18nedFields(), array('name', 'description'), '->getI18nedFields() returns the correct value');
  $t->is($product->getModelName(), 'Product', '->getModelName() returns the correct value');
