Written against v1.1.2.

The theme carries a class, `Institute_QuickLinks` in `setup/class.uams-quicklinks.php`, that registers a Quick Links menu and serves it as JSON for a script to draw. **It is off:** the line in `functions.php` that loads the class is commented out, so on a site running this theme neither the menu location nor the feed exists.

## What it does when loaded

| Part | Value |
| --- | --- |
| Menu location | `quick-links`, labeled Quick Links under Appearance, then Menus. |
| AJAX action | `quicklinks`, answered for visitors who are signed in and those who are not. |
| Menu it serves | The menu assigned to the `quick-links` location; failing that, a menu whose slug is `quick-links`; failing both, none. |
| Response | A JSON array with one object per menu item, or `[]` when there is no menu. |

Each object keeps five of the menu item's fields:

| Field | Type | Meaning |
| --- | --- | --- |
| `ID` | integer | The menu item's post ID. |
| `menu_item_parent` | string | The parent item's ID, or `"0"` for a top-level item. |
| `url` | string | Where the link goes. |
| `title` | string | The link text. |
| `classes` | array or `false` | The item's CSS classes, or `false` when the first one is empty. |

## Example

With the class loaded and a menu assigned, a request to a placeholder site:

```text
GET https://example.org/wp-admin/admin-ajax.php?action=quicklinks
```

answers:

```json
[
  {"ID": 101, "menu_item_parent": "0", "url": "https://example.org/forms/", "title": "Forms", "classes": false},
  {"ID": 102, "menu_item_parent": "101", "url": "https://example.org/forms/leave/", "title": "Leave request", "classes": ["external"]}
]
```

## Turning it on

Uncomment the `require` line for `setup/class.uams-quicklinks.php` in `functions.php` through a pull request. Nothing else in the theme calls the feed, so a script that reads it has to come with that change.
