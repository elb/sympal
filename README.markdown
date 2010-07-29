sfSympalPlugin
==============

This plugin is the core of the sympal CMF. It provides a base dynamic
routing and content layer.

The goal of this plugin is two-fold:

 1. To allow the end-user to associate a url with a set of content and
    a specific rendering method for that content.

 1. To accomplish this while minimizing the effect on the developer's workflow.

Off to a quick start
--------------------

The heart of sympal exists in the `sfSympalContent` and `sfSympalContentType`
records. Each `sfSympalContent` record has one `sfSympalContentType` record
which defines the url pattern (e.g. `/products/:slug`), rendering information
(e.g. the module/action) and the model that will be used to store all of
the content. While these models are critically important for creating the
dynamic routes behind each piece of content, you should, for the most part,
not need to interact with these models directly during normal workflow.

Setting up a content model
--------------------------

Let's begin by creating a basic `sfSympalPage` model:

    sfSympalPage:
      actAs:
        sfSympalContentTypeTemplate:
        Sluggable:
      columns:
        title:
          type: string(255)
          notnull: true
        body:   clob

The addition of the behavior above does a few things:

 * Add a `content_id` foreign key.
 * Relates `sfSympalContent` and `sfSympalPage` with a one-to-one relationship.
 * Automatically handles the content relations to `sfSympalContent`,
   `sfSympalContentType` and `sfSympalSite`. In other words, you can work
   with your model has normal and the details important to url creation
   and rendering are handled for you.

Creating a content type
-----------------------

As mentioned above, the sympal system is based around the idea of having
different types of content, or `content types`. Similar object routes,
each content type specifies the url (e.g. `/blog/:slug`) to the content
and the default rendering method (e.g. `myModule/myAction`). Each content
type is a mixture of a record on the `sfSympalContentType` model and data
stored in `app.yml`. This allows for most aspects of a content type to
be stored and modified by the developer, while allowing some aspects to
be modified by the end cms user.

When setting up a new content type, you only have to worry about the
`app.yml` configuration. The necessary record in `sfSympalContentType`
will be handled automatically. For example, the content type defined in
the `sfSympalPagesPlugin` looks like this:

    all:
      sympal_config:
        content_types:
          page:
            model:    sfSympalPage
            rendering_methods:
              default:
                template: sympal_page/view

The new content type, whose key is `page`, uses the `sfSympalPage` as its
content storage engine. It also specifies a default rendering method which
in this case, thanks to a helper action shipped with sympal, specifies that
the `sympal_page/view` partial should be rendered when a content record
of this type is matched. More information on the rendering of the content
is below.

Creating content
----------------

Apart from the content type configuration setup, creating content in sympal
is very straightforward:

    $page = new sfSympalPage();
    $page->title = 'My new page';
    $page->save();

And that's it! In the background, a one-to-one relationship has been created
from `sfSympalPage` to `sfSympalContent`. The `sfSympalContent` has also
been related to the `page` content type specified above. This happens automatically
because you've only configured one content type (`page`) for the model
`sfSympalPage`.

### The url to the content

The new `$page` object is now accessible via `/sf_sympal_page/my-new-page`.
This is because there is a `default_path` field in `sfSympalContentType`,
which defaults to, in this case, `/sf_sympal_page/:slug`. Recall that
`sfSympalPage` uses the `Sluggable` template, and so the slug is automatically
created for us. Now, let's change this to make the url more friendly:

    $page->Content->Type->default_path = '/pages/:slug';
    $page->Content->Type->save();

The `$page` object is now accessible via `/pages/my-new-page`.

### Custom url for the content

While the content type specifies a default path to your content, you can
also create a totally custom url on a content-by-content basis. This is
done via a `custom_path` field on `sfSympalContent`. Let's suppose that
we want our homepage to be dynamically built through the sympal system:

    $page->Content->custom_path = '/';
    $page->Content->save();

Your content is now available at `/` (the homepage).


Rendering the content
---------------------

Each content type can specify one or more `rendering methods` in the `app.yml`
configuration. Here are two rendering methods:

all:
  sympal_config:
    content_types:
      page:
        model:    sfSympalPage
        rendering_methods:
          default:
            template: sympal_page/view
          custom_action:
            module:   sympal_page
            action:   show

This highlights the two different ways to render content. The first is
by specifying either a component or a partial via the `template` key. Internally,
this routes to a special sympal module/action that ultimately renders
the content via the above component/partial.

When rendering via a partial, you automatically have access to a variable
representing your content. The variables is the "tableized" version of
your model. In the above example using `sfSympalPage`, the partial might
look like this:

    <h1><?php echo $sf_sympal_page->title ?></h1>

    <?php echo $sf_sympal_page->body ?>

The second type of rendering method is the more traditional module/action
pair. In this case, the route sends the request directly to the specified
module and action:

    public function executeShow(sfWebRequest $request)
    {
      $this->sf_sympal_page = $this->getRoute()->getObject();
      $this->sf_sympal_page->getSympalContentActionLoader();
    }

Like any normal symfony application, the first line retrieves your object
from the object route. The second line, while not strictly necessary,
allows sympal to perform several different actions:

 * Check to see that the content is published
 * Setup meta information on the response object (e.g. page title)

The template (`showSuccess.php`) now has access to the `sf_sympal_page`
variable as normal. From here, the template is identical to the above partial:

    <h1><?php echo $sf_sympal_page->title ?></h1>

    <?php echo $sf_sympal_page->body ?>


TODO
----

 * explain the value behind multiple sfSympalContentType records per content model

 * talk about the "template" strategy for rendering

 * explore and explain the idea and strategy behind binding the current
   context with the matched content record. How do we do this, what does
   it do for us.

 * corrolary to the above: how is all the metadata set (page title, keywords),
   etc?

 * cascading configuration: explain how the url and rendering methods cascade
   from the sfSympalContentType record down to the sfSympalContent record
