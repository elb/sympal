<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(29);
$tbl = Doctrine_Core::getTable('sfSympalContent');

$product = new Product();
$product->save();
$content = $product->Content;

$t->info('2 - Test some published functions.');

  $t->info('  2.1 - getIsPublished(), getIsPublishedInTheFuture()');
    $t->is($content->getIsPublished(), false, '->getIsPublished() is false if date_published is not set.');
    $t->is($content->getIsPublishedInTheFuture(), false, '->getIsPublishedInTheFuture() is false if date_published is not set.');
    $content->date_published = date('Y-m-d H:i:s', time() + 86400);
    $t->is($content->getIsPublished(), false, '->getIsPublished() is false if date_published is in the future.');
    $t->is($content->getIsPublishedInTheFuture(), true, '->getIsPublishedInTheFuture() is true if date_published is in the future.');
    $content->date_published = date('Y-m-d H:i:s', time() - 86400);
    $t->is($content->getIsPublished(), true, '->getIsPublished() is true if date_published is in the past.');
    $t->is($content->getIsPublishedInTheFuture(), false, '->getIsPublishedInTheFuture() is false if date_published is in the past.');

  $t->info('  2.2 - publish(), unpublish()');
    // refresh back to a blank date_published
    $content->refresh();
    $content->publish();
    $content->refresh(); // make sure it was saved
    $t->is($content->getIsPublished(), true, '->publish publishes the content.');
    $content->unpublish();
    $content->refresh();
    $t->is($content->getIsPublished(), false, '->publish unpublishes the content.');

  $t->info('  2.3 - Test getMonthPublished(), getDayPublished(), getYearPublished()');
    $content->date_published = '2010-06-05 00:00:00';
    $t->is($content->getMonthPublished(), '06', '->getMonthPublished() returns the correct value.');
    $t->is($content->getDayPublished(), '05', '->getDayPublished() returns the correct value.');
    $t->is($content->getYearPublished(), '2010', '->getYearPublished() returns the correct value.');
    

$t->info('3 - Test some basic functions.');

/*  $t->info('  3.1 - Test getModuleToRenderWith() & getModuleToRenderWith()');
    $t->is($content->getModuleToRenderWith(), $type->getModuleToRenderWith(), '->getModuleToRenderWith() returns the type\'s module if no module is explicitly set.');
    $content->module = 'unit_test';
    $t->is($content->getModuleToRenderWith(), 'unit_test', '->getModuleToRenderWith() returns the module set on the content record if set.');

    $t->is($content->getActionToRenderWith(), $type->getActionToRenderWith(), '->getActionToRenderWith() returns the type\'s action if no action is explicitly set.');
    $content->action = 'unit_test_action';
    $t->is($content->getActionToRenderWith(), 'unit_test_action', '->getActionToRenderWith() returns the action set on the content record if set.');*/

  $t->info('  3.2 - Test getUnderscoredSlug()');
    $tmp = new sfSympalContent();
    $tmp->slug = 'test-content';
    $t->is($tmp->getUnderscoredSlug(), 'test_content', '->getUnderscoredSlug() works correctly.');

  $t->info('  3.3 - Test getContentTypeClassName()');
    $t->is($content->getContentTypeClassName(), 'Product', '->getContentTypeClassName() returns correct value.');

  $t->info('  3.5 - Test getTemplateToRenderWith()');
    $t->info('Exception thrown when default_view is not set');
    try
    {
      $content->getTemplateToRenderWith();
      $t->fail('Exception not thrown.');
    }
    catch (sfException $e)
    {
      $t->pass('Exception thrown: '.$e->getMessage());
    }
    sfSympalConfig::set('content_types', 'Product', array(
      'content_templates' => array(
        'default_view' => array('template' => 'some/template'),
        'other_view'   => array('template' => 'other/template'),
      )
    ));

    $t->is($content->getTemplateToRenderWith(), 'some/template', '->getTemplateToRenderWith() returns default_view template if no template is set.');
    $content->template = 'other_view';
    $t->is($content->getTemplateToRenderWith(), 'other/template', '->getTemplateToRenderWith() returns the template that is set on sfSympalContent.');

$t->info('4 - Test the save() and delete() methods.');
  Doctrine_Query::create()->from('sfSympalContent')->delete()->execute();
  Doctrine_Query::create()->from('Product')->delete()->execute();

  $content = new sfSympalContent();
  $content->Type = $type;
  $content->save();
  $t->ok($content->relatedExists('Site'), '->save() automatically sets the Site relation if not set.');

  $content->delete();
  $productCount = Doctrine_Query::create()->from('Product')->count();
  $t->is($productCount, 0, 'The delete of sfSympalContent cascades onto the content type record.');


function test_create_new_bad_type(lime_test $t, $type)
{
  try
  {
    sfSympalContent::createNew($type);
    $t->fail('Exception now thrown');
  }
  catch (InvalidArgumentException $e)
  {
    $t->pass('Exception thrown ' . $e->getMessage());
  }
}