<?php

/**
 * PluginContent form.
 *
 * @package    form
 * @subpackage sfSympalContent
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentForm extends BasesfSympalContentForm
{
  public function setup()
  {
    parent::setup();

    $this->validatorSchema->setOption('allow_extra_fields', true);

    unset(
      $this['site_id'],
      $this['created_at'],
      $this['updated_at'],
      $this['last_updated_by_id'],
      $this['slots_list'],
      $this['links_list'],
      $this['assets_list'],
      $this['comments_list']  // this should actually not be here - think of something better later
    );

    // if the sfDoctrineSlotPlugin is present, add any slot fields
    if (class_exists('sfDoctrineSlotExtendedForm'))
    {
      $this->addSlotFields();
    }
  }
}
