sfSympalPlugin
==============

This plugin is the core of the sympal CMF. It provides a base dynamic
routing and content layer.

The goal of this plugin is two-fold:

 1. To allow the end-user to dynamically construct routes to different types of
    content (i.e. models). Instead of hardcoding `/blog/:slug` into
    `routing.yml`, why not allow the user to create a `Blog` object and
    map it to any url?

 1. To accomplish the above while minimizing the effect on the developer's workflow.

A normal symfony application
----------------------------

Every content-based consists of a group of models and a set of routes
linking those models to a module action pair. For example, suppose a project
has a `Product` model with a `slug` column. A common method would be to
create the following route to view each product:

    product_show:
      url:    /product/:slug
      class   sfDoctrineRoute
      param:  { module: product, action: show }
      option: { model: Product, type: object }

This plugin allows these routes to be built dynamically. Instead of
hardcoding the url of each `Product` at `/product/:slug`, your user can
construct any url for each `Product` object.

This functionality forms the basic framework for any content management system.

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

Content type models should feel very familiar: they are the same data
models you would build in an application with or without sympal. What sympal
offers is a way to connect each of these records to a dynamically build route.

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

