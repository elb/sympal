sfSympalPlugin
==============

This plugin is the core of the sympal CMF. It provides a base dynamic
routing and content layer.

The goal of this plugin is two-fold:

 1. To allow the end-user to associate a url with a set of content and
    a specific rendering method for that content.

 1. To accomplish this while minimizing the effect on the developer's workflow.

Quick off to a quick start
--------------------------

The heart of sympal exists in the `sfSympalContent` and `sfSympalContentType`
records. Each `sfSympalContent` record has one `sfSympalContentType` record
which defines the url pattern (e.g. `/products/:slug`), rendering information
(e.g. the module/action) and the model that will be used to store all of
the content.

### Setting up a content model

Let's begin by creating a basic `Product` model:

    Product:
      columns:
        name:    string(255)
        price:   double
      actAs:
        sfSympalContentModelTemplate:

The addition of the behavior above does a few things:

 * Add a `content_id` foreign key.
 * Relates `sfSympalContent` and `Product` with a one-to-one relationship.
 * Adds sharing of properties/methods between the related records.
 * Handles automatic creation of the relationship when creating new records.

### Creating a content type record

Next, let's create a new `sfSympalContentType` record:

    $type = new sfSympalContentType();
    $type->name          = 'Product';
    $type->label         = 'Books';
    $type->slug          = 'books';
    $type->default_path  = '/books/:slug';
    $type->module        = 'books';
    $type->action        = 'show';
    $type->save();

>**INFO**
>For each `sfSympalContentType` record in the database, a route is dynamically
>created with the url pattern specified by `default_path` and the given
>module & action. The route has a class of `sfDoctrineRoute` and links to
>the `sfSympalContent` model.

### Creating your first page (`sfSympalContent` record)

We're now ready to create our first dynamic page:

    $content = sfSympalContent::createNew('books');
    $content->name = 'More with symfony';
    $content->price = '$39.90';
    $content->save();

The `slug` field of `$content` is generated to be `more-with-symfony`,
meaning that our new page is now accessible via the url
`/books/more-with-symfony` and rendered via the `books/show` action.

In the background, the following occurred:

 * `$content` is automatically related to the `sfSympalContentType` record
   whose `slug` equals `books`.

 * A new `Product` record is created and linked to `$content` via their
   one-to-one relationship.

Notice that `sfSympalContent` does __not__ contain a `name` nor a `price`
field. Part of the power of sympal is that the `sfSympalContent` and
`Product` records act as one object. If a field or method is referenced
on `sfSympalContent` and does not exist, it will be passed to the
`Product` record. In this case, the `name` and `price` fields of `Product`
are set with the given data.

This is a key point in sympal: though you'll always be handling `sfSympalContent`
records directly, each can be used as if it were an instance of whatever
data model it refers to.

>**NOTE**
>You can reference the related content model directly from `sfSympalContent`
>by calling `$content->getRecord()`.

### Rendering the content

When the url `/books/more-with-symfony` is matched, it will be routed to
the `books` module and `show` action. Place the following code in the action:

    public function executeShow(sfWebRequest $request)
    {
      $this->content = $this->getRoute()->getObject();
    }

The template now has access to the `sfSympalContent` record and can be
used to output the content:

    <h1><?php echo $content->name ?></h1>

    <p>
      Buy now for only $<?php echo $content->price ?>
    </p>

Recall that the `$content` variable is an instance of `sfSympalContent`
and that the `name` and `price` fields are passed to and returned from
the related `Product` record.