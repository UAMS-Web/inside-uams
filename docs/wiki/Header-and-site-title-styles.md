Written against v1.1.2.

The theme's `style.css` loads after the parent's and changes only the header: the thin strip across the top, the UAMS patch, the links and buttons in the strip, and the site title. Everything below the header looks as the parent theme draws it.

## What changes

| Element | Selector | Change |
| --- | --- | --- |
| Thin strip | `.uams-thinstrip` | Background UAMS red, `#9D2235`, with 14px of top padding. Its header area is transparent and its container is positioned over it at full width. |
| UAMS patch | `.uams-thinstrip .uams-patch` | Moved from the right of the strip to the left (5% in), 60px high. |
| Links in the strip | `.uams-thinstrip ul.uams-thin-links li` | White text, `#F0EEEF` on hover and focus. Hidden unless the page carries the `uams-primary` class. |
| Search button | `.uams-thinstrip button.uams-search` | Gray, `#767475`, and `#5D5B5C` on hover and focus. |
| Quick links button | `.uams-thinstrip button.uams-quicklinks` | Its icon is drawn from the theme's own sprite, `assets/svg/inside-sprite.svg`. |
| Search and quick links box | `.uams-thinstrip div.align-right #search-quicklinks` | At least 245px wide. |
| Site title | `#site-title`, `#site-title h1 a` | Light weight (300), white, `#eee` on hover and focus, and indented 5% to line up with the patch. |

## Breakpoints

| Width | Change |
| --- | --- |
| 769px and wider | The strip is 87px high, the patch 200px wide, and the site title sits to the right of the patch, at most 55% of the width. |
| 769px to 991px | The patch and the site title move to 2% in, and the site title is at most 43% of the width. |
| 768px and narrower | The strip gets a gray top border, its links are hidden, the mobile menu toggle is white with an icon from the sprite, the quick links icon is repositioned, and the site title moves below the strip. |

Read `style.css` in the repository for the exact values.
