<?php
/**
 * Default components class for sympal.
 *
 * @package     sfSympalPlugin
 * @subpackage  components
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-02
 * @version     svn:$Id$ $Author$
 */
class Basesympal_defaultComponents extends sfComponents
{
  /**
   * Displays a form allowing the user to change his/her language
   *
   * @param sfWebRequest $request
   * @return void
   */
  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::getLanguageCodes()));
    unset($this->form[$this->form->getCSRFFieldName()]);
    $widgetSchema = $this->form->getWidgetSchema();
    $widgetSchema->setLabel('language', 'Select Language');
  }
}