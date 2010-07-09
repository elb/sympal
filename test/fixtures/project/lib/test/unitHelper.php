<?php

// creates a content type record
function create_content_type(lime_test $t, $name)
{
  $t->info('...create a content type for '.$name);
  $type = new sfSympalContentType();
  $type->name = $name;
  $type->label = $name;
  $type->save();

  return $type;
}

// creates a content record of a given type
function create_content(lime_test $t, $type)
{
  $t->info('...create a Content record linking to '.$type);
  $content = sfSympalContent::createNew($type);
  $content->save();

  return $content;
}

// attaches and returns a new Doctrine_Connection_Profiler
function create_doctrine_profiler(Doctrine_Connection $conn = null)
{
  $profiler = new Doctrine_Connection_Profiler();
  if ($conn === null)
  {
    $conn = Doctrine_Manager::connection();
  }
  $conn->addListener($profiler);

  return $profiler;
}

// passed in a Doctrine profiler, this returns the # of queries made to that profile
function count_queries(Doctrine_Connection_Profiler $profiler)
{
  $count = 0;
  foreach ($profiler as $event)
  {
    if ($event->getName() == 'execute')
    {
      $count++;
    }
  }

  return $count;
}

// creates an sfGuardUser instance
function create_guard_user($username, $data = array())
{
  $data = array_merge(array(
    'email_address' => 'ryan@thatsquality.com',
    'password'      => 'test',
  ), $data);

  $user = new sfGuardUser();
  $user->username = $username;
  $user->fromArray($data);
  $user->save();

  return $user;
}

function has_filter(lime_test $t, Doctrine_Record $record, $filterClass)
{
  foreach ($record->getTable()->getFilters() as $filter)
  {
    if (get_class($filter) == $filterClass)
    {
      $t->pass(sprintf('Filter %s is on this record', $filterClass));

      return;
    }
  }

  $t->fail(sprintf('Filter %s is NOT on this record', $filterClass));
}

function has_listener(lime_test $t, Doctrine_Record $record, $listenerClass)
{
  $i = 0;
  $listeners = array();
  while ($listener = $record->getTable()->getRecordListener()->get($i++))
  {
    if (get_class($listener) == $listenerClass)
    {
      $t->pass(sprintf('Listener %s is on this record', $listenerClass));

      return;
    }

    $listeners[] = get_class($listener);
  }

  $t->fail(sprintf('Listener %s is NOT on this record (%s)', $listenerClass, implode(', ', $listeners)));
}