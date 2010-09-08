<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$product = new Product();
$product->name = 'Some product';
$product->Content->meta_keywords = 'keyword test';
$product->Content->meta_description = 'foo bar';
$product->save();

$browser->info('  1.2 - Surf back to the no content route, with a content record in the db')
  ->get('/content-action-loader/content/fake')

  ->with('response')->begin()
    ->info('The request now receives a 404 response.')
    ->isStatusCode(404)
  ->end()
;

$browser->info('2 - Load a real piece of content')
  ->info('  2.1 - Load an unpublished piece of content')
  ->get('/content-action-loader/product/'.$product->id)

  ->with('request')->begin()
    ->isParameter('module', 'content_action_loader')
    ->isParameter('action', 'content')
  ->end()

  ->isForwardedTo('sympal_default', 'unpublished_content')

  ->with('response')->isStatusCode(200)

  ->info('  2.2 - Even unpublished, the content and site should be bound to the site manager')
;
$siteManager = $browser->getContext()
  ->getConfiguration()
  ->getPluginConfiguration('sfSympalPlugin')
  ->getSiteManager();

$browser->test()->is(get_class($siteManager->getSite()), 'sfSympalSite', 'The site record is set');
$browser->test()->is($siteManager->getCurrentContent()->id, $product->id, 'The current content record is set');

$browser->info('  2.3 - Load a real, published piece of content');
$product->Content->publish();
$browser->get('/content-action-loader/product/'.$product->id)

  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('head title', 'Some product | sympal')
  ->end()
;

$response = $browser->getContext()->getResponse()->getContent();
$browser->test()->ok(strpos($response, '<meta name="keywords" content="keyword test" />') !== false, 'The meta keywords are set.');
$browser->test()->ok(strpos($response, '<meta name="description" content="foo bar" />') !== false, 'The meta description is set.');

$browser->info('3 - Goto the url with an alternative sf_format, see that the request is faked out.')
  ->get('/content-action-loader/product/'.$product->id.'.xml')

  ->with('request')->begin()
    ->info('  3.1 - The sf_format is xml')
    ->isParameter('sf_format', 'xml')
  ->end()

  ->with('response')->begin()
    ->info('  3.2 - The status code is 200, because the normal template is able to render')
    ->isStatuscode(200)
  ->end()
;
$browser->test()->is($browser->getContext()->getRequest()->getRequestFormat(), 'html', 'The request has been faked into an html format');
