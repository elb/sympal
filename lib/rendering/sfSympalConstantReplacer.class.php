<?php

/**
 * helper class to allow us to fully utilize preg_replace_callback
 */
class sfSympalConstantReplacer
{
  public $content = null;

  public function replaceConstantsCallback($matches)
  {
    $field = strtolower($matches[1]);

    try
    {
      return $this->content->get($field);
    }
    catch (Doctrine_Record_UnknownPropertyException $e)
    {
    }

    return '%'.$field.'%';
  }

}