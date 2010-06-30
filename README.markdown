sfSympalPlugin
==============

This plugin is the core of the sympal CMF. It provides a base dynamic
routing and content layer.

The goal of this plugin is two-fold:

 1. To allow the end-user to associate a url with a set of content and
    a specific rendering method for that content.

 1. To accomplish this while minimizing the effect on the developer's workflow.

Mapping __urls__ to __content__ and a __rendering method__
----------------------------------------------------------

All content-based projects consists of content (database models) and a
set of routes mapping that content to a module action pair. For example:

    product_show:
      url:    /product/:slug
      class   sfDoctrineRoute
      param:  { module: product, action: show }
      option: { model: Product, type: object }

In this case, each url matching `/product/:slug` is associated with a
specific `Product` record (the __content__) and is mapped to the `product/show`
action for rendering.

This plugin allows these types of routes to be built dynamically. Instead of
hardcoding the url of a `Product` at `/product/:slug`, the user can construct
any url for and even customize the method used to render the content.

>By "rendering method" in sympal we mean either a module-action pair or
>a component/partial. In the first case, a route is created that routes
>directly to the given module-action pair. In the latter case, the route
>maps to a special module-action pair in sympal that renders the given
>partial/component.

The three models behind each request
------------------------------------

Each of dynamic page is powered by exactly three doctrine models.  

### 1) The base content model: `sfSympalContent`

At the heart of sympal is the `sfSympalContent` model. Each `sfSympalContent`
record corresponds to a unique url, maps to one specific set of content,
and contains specific directions on how the content should be rendered.
However, each of these tasks depends on the second model.

### 2) The content "type": `sfSympalContentType`

In that same way that each page of your site belongs to a specific section
(e.g. news, products, galleries), each `sfSympalContent` belongs to exactly
one `sfSympalContentType` record. The `sfSympalContentType` model is the
glue that holds the system together and specifies:

 * the default url to use for each `sfSympalContent` record (e.g. `/product/:slug`)

 * the default rendering method for each `sfSympalContent` record

 * the Doctrine model that will be used to store and retrieve all of the
   content for this `sfSympalContent` record (see the third model)

### 3) The content type model: can be __any__ Doctrine model

Whereas the first two models are packaged with sympal and define the url
and rendering method for each page, the third model comes from __your__
project and defines the actual content available in the action/template
that renders the page.

In our example, `Product` would be a __content type model__. Content type
models should feel very familiar: these are the same data models that would
make of the content of your site if you didn't use sympal. What sympal
offers is a way to connect each of these content records to a dynamically
built route.

In fact, the only thing that makes a __content type model__ different from
any other Doctrine model is that it has a one-to-one relationship with
`sfSympalContent` that is handled for you automatically by using the
`sfSympalContentTypeTemplate` template.

Let's follow with a real-world example.

Example: create a new `Product` page
------------------------------------

Let's assume you've created a simple `Product` model. To begin, let's add
the `sfSympalContentTypeTemplate` to our schema. Remember, this does nothing
more than create a `content_id` column and a relation to `sfSympalContent`
called `Content`:

    Product:
      actAs:
        sfSympalContentModelTemplate:
      columns:
        name:   string(255)
        price:  double

Before we create our first `sfSympalContent` record, we need to create
an `sfSympalContentType` record that maps the content to `Product`:

    $type = new sfSympalContentType();
    $type->name          = 'Product';
    $type->label         = 'Books';
    $type->slug          = 'books';
    $type->default_path  = '/books/:slug';
    $type->module        = 'books';
    $type->action        = 'show';
    $type->save();

At this point, Sympal is now injecting a new sfDoctrineRoute into the routing
system that matches the url `/books/:slug`, maps to the `books/show` action,
and makes the `sfSympalContent` record available in the action. 

We're ready to create our first dynamic page:

    $content = sfSympalContent::createNew('books');
    $content->name = 'More with symfony';
    $content->save();

The above code is equivalent to doing the following:

    $type = Doctrine_Core::getTable()->findOneBySlug('books');
    $content = new sfSympalContent();
    $content->Type = $type;
    $content->Product = new Product();
    $content->name = 'More with symfony';
    $content->save();

Behind the scenes, the `slug` is built as `more-with-symfony`, meaning that
this page can now be accessed via `/books/more-with-symfony`.

How content is stored and retrieved
-----------------------------------

As we've discussed, each `sfSympalContent` has a one-to-one relationship
with some Doctrine record that provides the actual content. The exact
model is determined by the `sfSympalContentType` that the `sfSympalContent`
belongs to. This is very powerful because it allows for each url to be
powered by different data and still related back to a central model.










The relationship between `sfSympalContent` and its content model
----------------------------------------------------------------


Content type models (pure data storage)
---------------------------------------

Like any content-based application, sympal revolves around a group of database
models that make up the content of your site (e.g. `Product`, `Blog`,
`PhotoGallery`, etc). Collectively, these Doctrine models are referred to
as "content type" models.

In sympal, these content type models are pure data stores. They do exactly
one job: hold the raw data used to render the site. Each model holds no
information about the route that should be generated to the content nor
the module/action or template that should be executed to render the content.



The routing model: `sfSympalContent`
------------------------------------

Now that we've established our raw content, let's examine how routes can
be dynamically created. We'll then show how these dynamic routes connect
to each record of your content type models.

In any CMS, there is one database object that maps to exactly one url.This
object allows for the end user to create new pages by creating new instances
of this database object.

In sympal, this base object is `sfSympalContent`. With the help of another
database model (`sfSympalContentType`, see below), each `sfSympalContent`
record contains all the information to build a dynamic route, including:

 * the url for the `sfSympalContent` record

 * a rendering method (module/action or template)

While `sfSympalContent` is the most important model in the system, it
is used to do just one thing: _generate a route_. That route is a doctrine
route whose object is the `sfSympalContent` record that was used in its
construction. Thus, in the action, `$this->getRoute()->getObject()` will
return the `sfSympalContent` object.

Tying data to the dynamic routes
--------------------------------

At this point, the user can create dynamic `sfSympalContent` records (routes)
that map to any module and action. Once at the action, we have access to
the matched `sfSympalContent` record via `->getRoute()->getObject()`.

However, `sfSympalContent` contains almost no content that could be used
to construct a dynamic page. What's worse, different sections of your site
may require drastically different types of content. How can we use our
`sfSympalContent` record to map to a wide variety of data?

### Linking routes (`sfSympalContent`) to data storage (content type models)

The answer is to link each `sfSympalContent` record to exactly one record
from one of your content type models. This has the effect to "extend" each
`sfSympalContent` record so that it has access to the data from one record
of one of your content type models.

Suppose an `sfSympalContent` object has a one-to-one relationship with a
`Blog` object under the alias `Record`. The resulting action might look
like this:

    public function executeShow(sfWebRequest $request)
    {
      $this->blog = $this->getRoute()->getObject()->Record;
    }

With this relationship, each `sfSympalContent` is joined with one row of
data that is used to populate the content for the page.

### Creating the `Record` relationship

However, creating this one-to-one relationship from `sfSympalContent` to
one of your content type models can prove tricky. Specifically,





    sfSympalContentType:
      product_books:
        name:         Product
        label:        Books
        slug:         books
        default_path: /books/:slug
        template:     books/view






Each `sfSympalContent` record has a one-to-one relationship with exactly
one other model that holds all of the content needed to render the page.
These models are collectively known as "content types" and can include any
model, such as `News`, `Product`, or `Gallery` models. Each record of each
content type model has a one-to-one relationship with an `sfSympalContent`
record. Together, both records represent a dynamic url and a dynamic set
of data.

With this basic setup, we already have the fundamental pieces for a very
powerful system. Specifically, __the user can create `Product`, `News`,
or `Gallery` objects and map them to custom urls.

