<?php

require_once(dirname(__FILE__).'/../../../bootstrap/functional.php');

$t = new lime_test(20);
$tbl = Doctrine_Core::getTable('sfSympalContent');

$product = new Product();
$product->save();
$content = $product->Content;
$type = $content->Type;

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

  $t->info('  3.1 - Test getModuleToRenderWith() & getModuleToRenderWith()');
    $t->is($content->getModuleToRenderWith(), $type->getTypeObject()->getModuleToRenderWith(), '->getModuleToRenderWith() returns the type\'s module if no module is explicitly set.');
    $content->rendering_method = 'custom_module';
    $t->is($content->getModuleToRenderWith(), 'product', '->getModuleToRenderWith() returns the module set on the content record if set.');

    $t->is($content->getActionToRenderWith(), $type->getTypeObject()->getActionToRenderWith(), '->getActionToRenderWith() returns the type\'s action if no action is explicitly set.');
    $content->rendering_method = 'custom_action';
    $t->is($content->getActionToRenderWith(), 'customAction', '->getActionToRenderWith() returns the action set on the content record if set.');

  $t->info('  3.3 - Test getContentTypeClassName()');
    $t->is($content->getContentTypeClassName(), 'Product', '->getContentTypeClassName() returns correct value.');

  $t->info('  3.5 - Test getTemplateToRenderWith()');
    $t->is($content->getTemplateToRenderWith(), 'product/view', '->getTemplateToRenderWith() returns default_view template if no template is set.');
    $content->rendering_method = 'other_view';
    $t->is($content->getTemplateToRenderWith(), 'product/other_view', '->getTemplateToRenderWith() returns the template that is set on sfSympalContent.');

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