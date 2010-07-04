<?php

/**
 * Doctrine record filter which notifies Symfony events to allow you to
 * easily add new functionality to a Doctrine model through the use of
 * symfony filter events.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalRecordEventFilter extends Doctrine_Record_Filter
{
  /**
   * @return void
   */
  public function init()
  {
    $this->_eventName = sfInflector::tableize($this->_table->getOption('name'));
  }

  /**
   * Hooks into the setter to throw a symfony event: sympal.my_table.method_not_found
   *
   * @throws Doctrine_Record_UnknownPropertyException
   * @return Doctrine_Record
   */
  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    $method = 'set'.sfInflector::camelize($name);
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($record, 'sympal.'.$this->_eventName.'.method_not_found', array('method' => $method, 'arguments' => array($value))));
    if ($event->isProcessed())
    {
      return $record;
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  /**
   * Hooks into the getter to throw a symfony event: sympal.my_table.method_not_found
   *
   * @throws Doctrine_Record_UnknownPropertyException
   * @return Doctrine_Record
   */
  public function filterGet(Doctrine_Record $record, $name)
  {
    $method = 'get'.sfInflector::camelize($name);
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($record, 'sympal.'.$this->_eventName.'.method_not_found', array('method' => $method)));
    if ($event->isProcessed())
    {
      return $event->getReturnValue();
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}