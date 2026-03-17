# Mega Menu Block

This plugin registers an experimental Mega Menu block for `core/navigation`.

## What It Includes

- Dynamic mega menu block (`outermost/mega-menu`) rendered via PHP.
- Interactivity API-powered open/close behavior and responsive positioning.
- Custom `menu` template part area for composing mega menu content in Site Editor.
- Navigation-level controls for hover-open mode and debug mode.
- Accessibility-oriented toggle/panel wiring (`aria-expanded`, `aria-controls`, `aria-hidden`, keyboard Escape close).

## Requirements

- WordPress 6.5+
- PHP 7.0+

## Notes

- Use with block themes.
- Vertical Navigation layouts are not currently supported.
- Menu widths are `content`, `wide`, or `full`.

## Development

- `npm run start` for development builds
- `npm run build` for production assets
- `npm run lint:js:src` and `npm run lint:css` for linting