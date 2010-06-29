sfSympalPlugin
==============

This plugin is the core of sympal CMF. It provides a base content and
routing layer. In other words, this plugin is a basic system for creating
database objects (`sfSympalContent`) which route to defined urls and
render using defined methods.

The routing model: `sfSympalContent`
------------------------------------

In any CMS, there is one database object that maps to exactly one url. This
object forms the base of the system and allows for the end user to create
new pages in the system by creating new instances of this database object.

In sympal, that base object is `sfSympalContent`. With the help of some
other database objects (see below), each `sfSympalContent` record contains
enough information to determine the following:

 * the url of the `sfSympalContent` record

 * a rendering method (module/action or template)

So, each `sfSympalContent` record holds all the directions necessary for
dynamically mapping a specific url to a configurable method of rendering.

In other words, each `sfSympalContent` record effectively represents a
dynamic symfony route. Keep this analogy in mind.

Bringing in some content
------------------------

At this point, the user can, of course, create dynamic `sfSympalContent`
records (routes) that map to any module and action. Once at the module/action,
we have full access to the `sfSympalContent` record matched via the url.
Unfortunately, other than a few fields like `page_title`, `meta_description`,
and `meta_keywords`, `sfSympalContent` doesn't contain a lot of dynamic
content that could be used to fill a page. What's worse, different sections
of your site may require drastically different types of data. How can we
use our `sfSympalContent` record to map to a wide variety of data?

### Introducing "content types"

Each `sfSympalContent` record has a one-to-one relationship with exactly
one other model that holds all of the content needed to render the page.
These models are collectively known as "content types" and can include any
model, such as `News`, `Product`, or `Gallery` models. Each record of each
content type model has a one-to-one relationship with an `sfSympalContent`
record. Together, both records represent a dynamic url and a dynamic set
of data.

With this basic setup, we already have the fundamental pieces for a very
powerful system. Specifically, __the user can create a new `Product`,
`News`, or `Gallery` item and map it to any url__.