Written against v1.1.2.

The theme prints its own breadcrumb trail in place of the parent's. The parent theme (`uams-2016`) defines `get_uams_breadcrumbs()` only when no theme has defined it yet, and a child theme's `functions.php` loads before the parent's, so this theme's version is the one the parent's `breadcrumbs.php` template prints through `uams_breadcrumbs()`.

## Output

The function returns one string:

```html
<nav class='uams-breadcrumbs' aria-label='breadcrumbs'><ul> ... </ul></nav>
```

Each crumb is one `<li>`, closed by `</li>`. In v1.1.1 the site crumb was followed by a second `<li>` and most other crumbs were left open; v1.1.2 closes every one ([UAMS-Web/inside-uams#70](https://github.com/UAMS-Web/inside-uams/issues/70)). A crumb that links is an `<a>` with a `title` attribute; the crumb for the view being shown carries `class="current"` and holds a `<span>` instead of a link.

## The crumbs on every view

| Crumb | When it appears | What it shows |
| --- | --- | --- |
| Home | Always, first | A link to the InsideUAMS front page. |
| The site | On every site except the network's main site | The site's title, linking to the site's home page; marked current on the site's front page. |

The parent theme's trail starts with a link to the main UAMS website instead, and names the site on every site including the main one. Those two rows are the difference between the two trails.

## The crumbs for each kind of view

| View | Crumbs after the two above |
| --- | --- |
| A page other than the front page | Each ancestor page, linked, then the current page. |
| A single post | Its first category, linked, when it has one; for a custom post type, its post type, linked to the archive (or to the post type's rewrite slug when it has no archive); then the post's title. |
| A category archive | The category's name. |
| A taxonomy archive | `<Taxonomy name>: <term name>`. |
| A taxonomy archive that is also a post type archive | The term's name. |
| A post type archive | The post type's menu name. |
| The posts page | Its title. |
| Search results | `Search results for <query>`. |
| An author archive | `Author: <display name>`. |
| A 404 | `Ooops!` |

## Example

A page titled Forms, whose parent is Human Resources, on a site titled Example Site:

```html
<nav class='uams-breadcrumbs' aria-label='breadcrumbs'><ul>
  <li><a href="..." title="...">Home</a></li>
  <li><a href="https://example.org/" title="Example Site">Example Site</a></li>
  <li><a href="https://example.org/human-resources/" title="Human Resources">Human Resources</a></li>
  <li class="current"><span>Forms</span></li>
</ul></nav>
```

The real output has no line breaks between the crumbs.
