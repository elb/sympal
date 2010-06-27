<?php

class sfSympalDataGridPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactories'));

    self::_markClassesAsSafe();
  }

  public function listenToContextLoadFactories(sfEvent $event)
  {
    sfSympalDataGrid::setSymfonyContext($event->getSubject());
  }

  /**
   * Mark classes safe from the output escaper
   *
   * @return void
   */
  private static function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalDataGrid',
    ));
  }
}