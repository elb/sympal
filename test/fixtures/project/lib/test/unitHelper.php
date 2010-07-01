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