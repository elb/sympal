<?php

class sfSympalContentTypeListener extends Doctrine_Record_Listener
{
  /**
   * @var array
   */
  protected $_options = array();

  /**
   * Class constructor
   *
   * @param  $options
   * @return void
   */
  public function __construct(array $options)
  {
    $this->_options = $options;
  }

  /**
   * Sets the $record->Content->ContentType relation correctly if not set.
   *
   * @param Doctrine_Event $event
   * @return void
   */
  public function preSave(Doctrine_Event $event)
  {
    $record = $event->getInvoker();

    // if the content type appears to already be setup, leave it.
    if ($record['Content']['Type']['key'])
    {
      return;
    }

    // find a content type key by the model
    $key = sfSympalConfig::getContentTypeKeyFromModel($record->getTable()->getOption('name'));
    $contentType = Doctrine_Core::getTable('sfSympalContentType')->getOrCreateType($key);

    // attach the content type to the Content record
    $record['Content']['Type'] = $contentType;
  }
}