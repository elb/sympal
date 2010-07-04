<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
function config_cleanup()
{
  chdir(dirname(__FILE__).'/../../fixtures/project');
  exec('git checkout config/app.yml');
  exec('git checkout apps/sympal/config/app.yml');
}
register_shutdown_function('config_cleanup');

$t = new lime_test(17);

$t->info('1 - Test the basic get(), getDeep() and set() methods');
  sfSympalConfig::set('test', true);
  $t->is(sfSympalConfig::get('test'), true, '->get() works with just one argument');

  sfSympalConfig::set('group', 'test', true);
  $t->is(sfSympalConfig::get('group', 'test'), true, '->get() works using the group arugment');

  $t->is(sfSympalConfig::get('doesnt_exists', null, 'default_value'), 'default_value', '->get() returns a default value if the key does not exist');

  sfConfig::set('app_sympal_config_test', array('subgroup' => array('name' => 'sympal')));
  $t->is(sfSympalConfig::getDeep('test', 'subgroup', 'name', 'default'), 'sympal', '::getDeep() returns the correct existing value.');
  $t->is(sfSympalConfig::getDeep('test', 'subgroup', 'fake', 'default'), 'default', '::getDeep() returns the default value if the config does not exist.');

$t->info('2 - Test the writeSetting() method');
  sfSympalConfig::writeSetting('test_write_value', 1);
  $path = sfConfig::get('sf_config_dir').'/app.yml';
  $array = (array) sfYaml::load(file_get_contents($path));
  $t->is(isset($array['all']['sympal_config']['test_write_value']), true, '->writeSetting() writes out correctly to the config/app.yml file');

$t->info('3 - Test the getCurrentSiteName() method');
  $t->info('  3.1 - No current site will be set, so an exception with be thrown');
  try
  {
    sfSympalConfig::getCurrentSiteName();
    $t->fail('Exception not thrown');
  }
  catch (sfException $e)
  {
    $t->pass('Exception thrown');
  }

  $t->info('  3.2 - Test the getCurrentSiteName() method.');
  sfConfig::set('sf_app', 'site_name');
  $t->is(sfSympalConfig::getCurrentSiteName(), 'site_name', '->getCurrentSiteName() returns the sf_app value');
  sfConfig::set('sf_sympal_site', 'other_site_name');
  $t->is(sfSympalConfig::getCurrentSiteName(), 'other_site_name', '->getCurrentSiteName() returns the sf_sympal_site value');

$t->info('3 - Test the isI18nEnabled(), getLanguageCodes() methods');
  $t->info('  3.1 - Test isI18nEnabled() without passing an argument');
    sfConfig::set('sf_i18n', false);
    $t->is(sfSympalConfig::isI18nEnabled(), false, '::isI18nEnabled() returns false when sf_i18n is false');
    sfConfig::set('sf_i18n', true);
    $t->info('If sf_i18n is true but no language codes are set, we throw an exception to alert the developer');
    try
    {
      sfSympalConfig::isI18nEnabled();
      $t->fail('Exception not thrown');
    }
    catch (sfException $e)
    {
      $t->pass('Exception thrown');
    }
    sfSympalConfig::set('language_codes', array('en', 'es'));
    $t->is(sfSympalConfig::isI18nEnabled(), true, '::isI18nEnabled() is true when sf_i18n is true and lanuage codes are set.');

  $t->info('  3.2 - Test isI18nEnabled() for a particular model');
    $t->is(sfSympalConfig::isI18nEnabled('Product'), false, '::isI18nEnabled(Product) returns false - it\'s not in the internationalized_models array');
    sfSympalConfig::set('internationalized_models', 'Product', array('title'));
    $t->is(sfSympalConfig::isI18nEnabled('Product'), true, '::isI18nEnabled(Product) returns true - it\'s in the internationalized_models array');

  $t->info('  3.3 - Test the getLanguageCodes() method');
    $t->is(sfSympalConfig::getLanguageCodes(), array('en', 'es'), '::getLanguageCodes() returns the correct language codes.');

$t->info('4 - Test the ::getContentTemplates() method');
  sfConfig::set('app_sympal_config_content_types', array(
    'Product' => array(
      'content_templates' => array(
        'default_view' => 'foo/bar',
        'register'     => 'bar/foo',
      )
    )
  ));
  $contentTemplates = sfSympalconfig::getContentTemplates('Product');
  $t->is(isset($contentTemplates['default_view']), true, '::getContentTemplates() returns default_view for Product');
  $t->is(isset($contentTemplates['register']), true, '-::getContentTemplates() returns register for Product'); 