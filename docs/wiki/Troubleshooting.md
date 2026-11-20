Written against v1.1.2.

- [The Themes screen says the parent theme is missing](#the-themes-screen-says-the-parent-theme-is-missing)
- [The breadcrumb trail starts at the main UAMS website](#the-breadcrumb-trail-starts-at-the-main-uams-website)
- [The breadcrumb trail does not name the site](#the-breadcrumb-trail-does-not-name-the-site)
- [A request for the quick links feed answers 0](#a-request-for-the-quick-links-feed-answers-0)

## The Themes screen says the parent theme is missing

`The parent theme is missing. Please install the "uams-2016" parent theme.`

**Cause:** this is a child theme, and its stylesheet names `uams-2016` as its template. WordPress cannot use it without that theme installed beside it, in a folder named `uams-2016`.

**Fix:** install the UAMS 2016 theme first, as the [README's Installation section](https://github.com/UAMS-Web/inside-uams#installation) describes, then reload the Themes screen.

## The breadcrumb trail starts at the main UAMS website

The first crumb, Home, links to the main UAMS website rather than to the InsideUAMS front page.

**Cause:** that is the parent theme's trail. The site is running `uams-2016` itself, or another theme, not this one.

**Fix:** activate Puget Sound on the site, as [Activating the theme on a site](Activating-the-theme-on-a-site.md) describes. See [Breadcrumbs](Breadcrumbs.md) for the trail it prints.

## The breadcrumb trail does not name the site

On the network's main site, the trail goes straight from Home to the page.

**Cause:** this is intended. The theme adds the site crumb only on sites other than the main one, since the main site's Home crumb already names it.

**Fix:** none needed.

## A request for the quick links feed answers 0

A request to `admin-ajax.php?action=quicklinks` answers `0` with status `400`.

**Cause:** WordPress answers `0` to an AJAX action nothing has registered, and the feed is off: the line that loads its class is commented out in `functions.php`.

**Fix:** see [Quick links feed](Quick-links-feed.md#turning-it-on).
