<?php

/**
 * Toolkit for form helper methods
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalFormToolkit
{
  /**
   * Embed i18n to the given form if it is enabled
   *
   * @param string $name 
   * @param sfForm $form 
   * @return void
   */
  public static function embedI18n($name, sfForm $form)
  {
    if (sfSympalConfig::isI18nEnabled($name))
    {
      $context = sfContext::getInstance();
      $culture = $context->getUser()->getEditCulture();
      $form->embedI18n(array(strtolower($culture)));
      $widgetSchema = $form->getWidgetSchema();
      $context->getConfiguration()->loadHelpers(array('Helper'));

      $c = sfCultureInfo::getInstance($culture);
      $languages = $c->getLanguages();
      $language = isset($languages[$culture]) ? $languages[$culture] : '';
      $widgetSchema[$culture]->setLabel($language);
    }
  }

  /**
   * Change the content slot type widget to be a dropdown
   *
   * @param sfForm $form 
   * @param boolean $blank Add a blank option
   * @return void
   */
  public static function changeContentSlotTypeWidget(sfForm $form, $blank = false)
  {
    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();
    $slotTypes = (sfSympalConfig::get('content_slot_types', null, array()));
    $choices = array();
    if ($blank)
    {
      $choices[''] = '';
    }
    foreach ($slotTypes as $key => $value)
    {
      $choices[$key] = $value['label'];
    }

    // unset the Column type for non-column slots
    if ($form instanceof sfSympalContentSlotForm)
    {
      unset($choices['Column']);
    }
    
    $widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => $choices));
    $validatorSchema['type'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys($choices)));
  }

  /**
   * Change the content choice widget to be a formatted/indented list
   *
   * @param sfForm $form 
   * @param boolean $add Add a new widget instead of trying replacing
   * @return void
   */
  public static function changeContentWidget(sfForm $form, $add = null)
  {
    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();
    if (is_null($add))
    {
      $key = isset($widgetSchema['content_id']) ? 'content_id' : 'content_list';
    } else {
      $key = $add;
    }
    if ((isset($widgetSchema[$key]) && $widgetSchema[$key] instanceof sfWidgetFormDoctrineChoice) || $add)
    {
      $q = Doctrine_Core::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->leftJoin('c.MenuItem m')
        ->where('c.site_id = ?', sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId())
        ->orderBy('m.root_id, m.lft');

      if ($add)
      {
        $widgetSchema[$key] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent'));
        $validatorSchema[$key] = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent', 'required' => false));
      }

      $widgetSchema[$key]->setOption('query', $q);
      $widgetSchema[$key]->setOption('method', 'getIndented');
    }
  }

  /**
   * Change date widgets to jquery rich date widget
   *
   * @param string $name
   * @param sfForm $form 
   * @return void
   */
  public static function changeDateWidget($name, sfForm $form)
  {
    sfApplicationConfiguration::loadHelpers('Sympal');
    sympal_use_javascript('jquery.ui.js');
    sympal_use_stylesheet('jquery.ui.css');

    $widgetSchema = $form->getWidgetSchema();
    $widgetSchema[$name] = new sfWidgetFormJQueryDate();
  }

  /**
   * Change the content slot form value widget
   *
   * @param string $type The type of the widget
   * @param sfForm $form The form whose slot will be modified
   * @param string $fieldName The name of the "slot" field on the form
   * @return void
   */
  public static function changeContentSlotValueWidget(sfSympalContentSlot $slot, sfForm $form)
  {
    // in case the type is blank
    $type = ($slot->type) ? $slot->type : 'Text';
    
    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();
    $contentSlotTypes = sfSympalConfig::get('content_slot_types', null, array());
    $options = isset($contentSlotTypes[$type]) ? $contentSlotTypes[$type] : array();

    $widgetClass = isset($options['widget_class']) ? $options['widget_class'] : 'sfWidgetFormSympal'.$type;
    $widgetOptions = isset($options['widget_options']) ? $options['widget_options'] : array();

    $validatorClass = isset($options['validator_class']) ? $options['validator_class'] : 'sfValidatorFormSympal'.$type;
    $validatorOptions = isset($options['validator_options']) ? $options['validator_options'] : array();
    $validatorOptions['required'] = false;
    
    /*
     * Setup the widget and validator: 3 cases:
     *   1) widget_class & is validator_class are not false, so we setup widget/validator using those
     *   2) widget_class & validator_class ARE false, the slot is a column - get the widget/validator from the content form
     *   3) All else fails, leave widget & validator alone
     */
    if ($widgetClass && $validatorClass)
    {
      // If this is a column slot, then its actual field name is the name of the slot
      $fieldName = $slot->is_column ? $slot->name : 'value';
      
      $widgetSchema[$fieldName] = new $widgetClass($widgetOptions, array('class'=> 'slot_'.strtolower($type)));
      $validatorSchema[$fieldName] = new $validatorClass($validatorOptions);
    }
  }
}
