<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginEntity extends BaseEntity
{
  protected
    $_allPermissions;

  public function getAllPermissions()
  {
    if (!$this->_allPermissions)
    {
      $this->_allPermissions = array();
      foreach ($this->Groups as $group)
      {
        foreach ($group->Permissions as $permission)
        {
          $this->_allPermissions[] = $permission->name;
        }
      }
      foreach ($this->Permissions as $permission)
      {
        $this->_allPermissions[] = $permission->name;
      }
    }
    return $this->_allPermissions;
  }

  public function __toString()
  {
    return $this->getHeaderTitle();
  }

  public function getTitle()
  {
    return $this->getHeaderTitle();
  }

  public function getMainMenuItem()
  {
    if ($this->master_menu_item_id)
    {
      return $this->MasterMenuItem;
    } else {
      $menuItem = $this->_get('MenuItem');
      if ($menuItem && $menuItem->exists())
      {
        return $menuItem;
      } else {
        $q = Doctrine::getTable('MenuItem')
          ->createQuery('m')
          ->where('m.has_many_entities = ?', true)
          ->andWhere('m.entity_type_id = ?', $this->entity_type_id);
        return $q->fetchOne();
      }
    }
  }

  public function getRecord()
  {
    if ($this['Type']['name'])
    {
      Doctrine::initializeModels(array($this['Type']['name']));
      return $this[$this['Type']['name']];
    } else {
      return false;
    }
  }

  public function getTemplate()
  {
    if ($this->entity_template_id)
    {
      return $this->_get('Template');
    }
    return $this->Type->getTemplate('View');
  }

  public function preValidate($event)
  {
    $invoker = $event->getInvoker();
    $modified = $invoker->getModified();
    if (isset($modified['is_published']) && $modified['is_published'] && !isset($modified['date_published']))
    {
      $invoker->date_published = new Doctrine_Expression('NOW()');
    }

    if (sfContext::hasInstance())
    {
      $user = sfContext::getInstance()->getUser();
      if ($user->isAuthenticated())
      {
        $invoker->last_updated_by = $user->getGuardUser()->getId();
        if (!$invoker->exists() || !$invoker->created_by)
        {
          $invoker->created_by = $user->getGuardUser()->getId();
        }
      }
      $invoker->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();
    }
  }

  public function releaseLock()
  {
    $this->locked_by = null;
    $this->save();
  }

  public function obtainLock(sfGuardUser $user)
  {
    $this->LockedBy = $user;
    $this->save();
  }

  public function userHasLock($user = null)
  {
    return $user && $this['locked_by'] == $user['id'];
  }

  public function publish()
  {
    $this->is_published = true;
    $this->date_published = new Doctrine_Expression('NOW()');
    $this->save();
    $this->refresh();
  }

  public function unpublish()
  {
    $this->is_published = false;
    $this->date_published = null;
    $this->save();
  }

  public function getHeaderTitle()
  {
    if ($record = $this->getRecord())
    {
      $guesses = array('name',
                       'title',
                       'username',
                       'subject');

      // we try to guess a column which would give a good description of the object
      foreach ($guesses as $descriptionColumn)
      {
        try
        {
          return (string) $record->get($descriptionColumn);
        } catch (Exception $e) {}
      }
    }

    return sprintf('No description for object of class "%s"', $this->getTable()->getComponentName());
  }

  public function getRoute()
  {
    if ($this['custom_path'])
    {
      return '@sympal_entity_' . $this['id'];
    } else if ($this['Type']['view_route_url']) {
      $route = new sfRoute($this['Type']['view_route_url']);
      $variables = $route->getVariables();
      $values = array();
      foreach (array_keys($variables) as $name)
      {
        try {
          $values[$name] = $this->$name;
        } catch (Exception $e) {}
      }
      return '@sympal_entity_view_type_' . $this['Type']['slug'] . '?' . http_build_query($values);
    } else {
      throw new sfException('Entity has invalid route.');
    }
  }

  public function getLayout()
  {
    if ($layout = $this->_get('layout')) {
      return $layout;
    } else if ($layout = $this->getType()->getLayout()) {
      return $layout;
    } else if ($layout = $this->getSite()->getLayout()) {
      return $layout;
    } else {
      return sfSympalConfig::get('default_layout', null, $this->getSite()->getSlug());
    }
  }
}