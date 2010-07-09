<?php

/**
 * Allows certain fields to be passed from a content type model to the
 * related sfSympalContent record.
 * 
 * @package     sfSympalPlugin
 * @subpackage  behaviors
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfSympalContentTypeFilter extends Doctrine_Record_Filter
{
  /**
   * @var array The field names to be passed to ->Content
   */
  protected $_fields = array();

  /**
   * Class constructor
   *
   * @param  array $fields
   * @return void
   */
  public function __construct($fields)
  {
    $this->_fields = $fields;
  }

  /**
   * Passes any "sets" to the ->Content record for all fields in the
   * fields array
   */
  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    if ($contentName = $this->_isFieldEligible($name))
    {
      return $record->Content->set($name, $value);
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  /**
   * Passes any "gets" to the ->Content record for all fields in the
   * fields array
   */
  public function filterGet(Doctrine_Record $record, $name)
  {
    if ($this->_isFieldEligible($name))
    {
      return $record->Content->get($name);
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  /**
   * Returns whether or not the given field should be get/set on the
   * related Content record
   *
   * @param  string $name The field name
   * @return bool
   */
  protected function _isFieldEligible($name)
  {
    return in_array($name, $this->_fields);
  }
}