# sf-ux-perfs-twig-components

A Symfony 8.1 app whose homepage is an admin dashboard built entirely from Symfony UX Toolkit's Shadcn kit components (HTML syntax): sidebar, tables, dropdown menus, dialogs, form fields, icons. It exists to profile and speed up Symfony UX TwigComponent, and the Twig/UX Icons code it relies on. The default page (25 rows) renders about 1,800 components and 400 icons per request; `?perPage=` (10 to 500) scales the posts table. Data is 500 fake posts generated in memory from a fixed seed, so every request renders the same HTML: no database.

## Run it

```
composer install
pnpm install
pnpm build # or pnpm dev for the Vite dev server
symfony serve -d
```

Assets are built with Symfony Reprise, Vite, Tailwind CSS v4, and pnpm. Open the site at the URL `symfony serve` prints.

## Testing local Symfony UX changes

From a symfony/ux clone, `php link /path/to/this/project` symlinks the UX packages into `vendor/` (`--rollback` undoes it). Clear the cache after any change that affects how templates compile.

## Xdebug and Blackfire

`php.ini` is loaded by the Symfony CLI (`symfony php`, `symfony console`, `symfony serve`) and sets `xdebug.mode=off`, so a globally loaded Xdebug doesn't skew measurements. `blackfire run symfony php script.php` profiles the helper process the CLI starts first, not the script. Run `php` directly with the ini scan dir the CLI uses instead:

```
blackfire run env PHP_INI_SCAN_DIR="$(symfony php -r 'echo getenv("PHP_INI_SCAN_DIR");')" php script.php
```
