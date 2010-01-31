<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContentSlot extends BasesfSympalContentSlot
{
  protected
    $_contentRenderedFor,
    $_rendered;

  public function setContentRenderedFor(sfSympalContent $content)
  {
    $this->_contentRenderedFor = $content;
  }

  public function getContentRenderedFor()
  {
    return $this->_contentRenderedFor;
  }

  public function resetRenderCache()
  {
    $this->_rendered = null;
  }

  public function getSlotEditFormRenderer()
  {
    $contentSlotTypes = sfSympalConfig::get('content_slot_types');
    return isset($contentSlotTypes[$this->type]['form_renderer']) ? $contentSlotTypes[$this->type]['form_renderer'] : sfSympalConfig::get('inline_editing', 'default_form_renderer', 'sympal_edit_slot/slot_editor_renderer');
  }

  public function getEditForm()
  {
    if ($this->is_column)
    {
      $form = $this->_getContentSlotColumnForm();
    } else {
      $contentSlotTypes = sfSympalConfig::get('content_slot_types');
      $className = isset($contentSlotTypes[$this->type]['form']) ? $contentSlotTypes[$this->type]['form'] : sfSympalConfig::get('inline_editing', 'default_slot_form', 'sfSympalInlineEditContentSlotForm');
      $form = new $className($this);
    }
    $form->getWidgetSchema()->setNameFormat('sf_sympal_content_slot_'.$this->id.'[%s]');
    return $form;
  }

  protected function _getContentSlotColumnForm()
  {
    $content = $this->getContentRenderedFor();
    $contentTable = $content->getTable();

    if ($contentTable->hasField($this->name))
    {
      $formClass = sfSympalConfig::get('inline_editing', 'default_column_form');
      $form = new $formClass($content);
      $form->useFields(array($this->name));
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $contentTranslationTable = Doctrine::getTable('sfSympalContentTranslation');
      if ($contentTranslationTable->hasField($this->name))
      {
        $formClass = sfSympalConfig::get('inline_editing', 'default_column_form');
        $form = new $formClass($content);
        $form->useFields(array(sfContext::getInstance()->getUser()->getCulture()));
      }      
    }

    $contentTypeClassName = $content->getContentTypeClassName();
    $contentTypeFormClassName = sfSympalConfig::get($contentTypeClassName, 'default_inline_editing_column_form', $contentTypeClassName.'Form');
    $contentTypeTable = Doctrine_Core::getTable($contentTypeClassName);
    if ($contentTypeTable->hasField($this->name))
    {
      $form = new $contentTypeFormClassName($content->getRecord());
      $form->useFields(array($this->name));
    }

    if (sfSympalConfig::isI18nEnabled($contentTypeClassName))
    {
      $contentTypeTranslationClassName = $contentTypeClassName.'Translation';
      $contentTypeTranslationFormClassName = sfSympalConfig::get($contentTypeTranslationClassName, 'default_inline_editing_column_form', $contentTypeTranslationClassName.'Form');
      $contentTypeTranslationTable = Doctrine_Core::getTable($contentTypeTranslationClassName);
      if ($contentTypeTranslationTable->hasField($this->name))
      {
        $form = new $contentTypeFormClassName($content->getRecord()); 
        $i18nForm = $form->getEmbeddedForm($language = sfContext::getInstance()->getUser()->getCulture()); 
        $i18nForm->useFields(array($this->name)); 
        unset($form[$language]); 
        $form->embedForm($language, $i18nForm); 
        $form->useFields(array($language)); 
      }
    }

    if (!$form)
    {
      throw new InvalidArgumentException('Invalid content slot');
    }

    return $form;
  }

  public function render()
  {
    if (!$this->_rendered)
    {
      $renderer = $this->getSlotRenderer();
      $rendered = (string) $renderer;
  
      $this->_rendered = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.content_renderer.filter_slot_content'), $rendered)->getReturnValue();
    }
    return $this->_rendered;
  }
  
  /**
   * Returns an instance of the renderer class for this content slot
   * 
   * @return sfSympalContentSlotRenderer
   */
  public function getSlotRenderer()
  {
    $contentSlotTypes = sfSympalConfig::get('content_slot_types');
    $className = isset($contentSlotTypes[$this->type]['renderer']) ? $contentSlotTypes[$this->type]['renderer'] : 'sfSympalContentSlot'.$this->type.'Renderer';
    
    return new $className($this);
  }

  public function setValue($value)
  {
    if ($this->is_column)
    {
      $name = $this->name;
      $this->_contentRenderedFor->$name = $value;
    }

    $this->_rendered = null;
    return $this->_set('value', $value);
  }

  public function getRawValue()
  {
    if ($this->is_column)
    {
      $value = $this->_contentRenderedFor->get($this->name);
    }
    else
    {
      $value = $this->getValue();
    }

    if (sfSympalContext::getInstance()->shouldLoadFrontendEditor() && !$value)
    {
      $rawValue = '[Double click to enable inline edit mode.]';
    }
    else
    {
      $rawValue = $value;
    }

    return $rawValue;
  }

  public function hasValue()
  {
    return trim(strip_tags($this->render()));
  }
}