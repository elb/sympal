<?php

/**
 * PluginContentType form.
 *
 * @package    form
 * @subpackage sfSympalContentType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentTypeForm extends BasesfSympalContentTypeForm
{
  public function setup()
  {
    parent::setup();
    
/*  @TODO replace this with something not invasive
    $field = sfApplicationConfiguration::getActive()
      ->getPluginConfiguration('sfThemePlugin')
      ->getThemeToolkit()
      ->getThemeWidgetAndValidator();
    $this->widgetSchema['theme'] = $field['widget'];
    $this->validatorSchema['theme'] = $field['validator'];*/

    $this->widgetSchema['name']->setLabel('Model name');

    // Sets up the template widget
    sfSympalToolkit::changeTemplateWidget($this);

    // Sets up the module widget
    //sfSympalToolkit::changeModuleWidget($this);
    

    // get the content type models and make an array where the keys are the values
    $models = array_keys(sfSympalConfig::get('content_types', null, array()));
    $models = array_combine(array_values($models), array_values($models));

    foreach ($models as $model)
    {
      $table = Doctrine_Core::getTable($model);
      if (!$table->hasTemplate('sfSympalContentTypeTemplate'))
      {
        unset($models[$model]);
      }
    }

    $models = array_merge(array('' => ''), $models);
    $this->widgetSchema['name'] = new sfWidgetFormChoice(array('choices' => $models));
  }
}