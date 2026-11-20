Written against v1.1.2.

At the end of this page a site on the network shows the Puget Sound header and the InsideUAMS breadcrumb trail.

**Note:** this assumes the parent theme `uams-2016` and this theme are both installed, as the [README's Installation section](https://github.com/UAMS-Web/inside-uams#installation) describes.

1. **In Network Admin, open Themes and choose Network Enable under Puget Sound.** The theme is now offered to every site on the network. On a single-site install, skip this step.
2. **On the site, open Appearance, then Themes, and activate Puget Sound.** The Themes screen lists it with UAMS 2016 as its parent.
3. **Load any page of the site.** The thin strip across the top is UAMS red (`#9D2235`), the UAMS patch sits at the left of the strip, and the site title is white, beside the patch on a desktop and below the strip on a phone. The [Header and site title styles](Header-and-site-title-styles.md) page lists every change.
4. **Open a page that has a parent page.** The breadcrumb trail starts with Home, linking to the InsideUAMS front page, then names the site, then each parent page, and ends with the current page. The [Breadcrumbs](Breadcrumbs.md) page gives the trail for every kind of view.

If the Themes screen reports the parent theme missing, or the trail starts somewhere else, see [Troubleshooting](Troubleshooting.md).
