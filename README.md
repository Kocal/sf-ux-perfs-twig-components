# sf-ux-perfs-twig-components

A Symfony 8.1 app whose homepage is an admin dashboard built entirely from Symfony UX Toolkit's Shadcn kit components (HTML syntax): sidebar, tables, dropdown menus, dialogs, form fields, icons. It exists to profile and speed up Symfony UX TwigComponent, and the Twig/UX Icons code it relies on. The default page (25 rows) renders about 1,800 components and 400 icons per request; `?perPage=` (10 to 500) scales the posts table. Data is 500 fake posts generated in memory from a fixed seed, so every request renders the same HTML: no database.

## Results

The performance pull requests measured together so far on the default page (25 rows) against Symfony UX 3.x and Twig 3.30.0:

- Symfony UX: [#4046](https://github.com/symfony/ux/pull/4046), [#4047](https://github.com/symfony/ux/pull/4047), [#4048](https://github.com/symfony/ux/pull/4048), [#4049](https://github.com/symfony/ux/pull/4049), [#4050](https://github.com/symfony/ux/pull/4050)
- Twig: [#4982](https://github.com/twigphp/Twig/pull/4982), [#4983](https://github.com/twigphp/Twig/pull/4983), [#4984](https://github.com/twigphp/Twig/pull/4984)

| Measure | Before | After | Change |
|---|---|---|---|
| CPU median per request | **46.0-48.0 ms** | **33.5-35.1 ms** | -27% |
| Blackfire wall time (5 samples of 5 requests) | **2.44 s** | **2.11 s** | -14% |
| Blackfire memory | 20.0 MB | 16.7 MB | -17% |
| `debug_backtrace()` calls per request | 1,317 | 38 | -97% |
| Mount hooks (`ComponentFactory::preMount()`) per request | 1,767 | 0 | -100% |
| Icon registry lookups per request | 414 | 90 | -78% |

CPU times are the median of 6 interleaved runs of 60 requests per side, in the prod environment with a fresh kernel and, like PHP-FPM, empty static caches per request; the rendered HTML is identical byte for byte. Blackfire profiles: [before](https://app.blackfire.io/envs/5f4f9a62-eaa0-45ee-b7b3-a1b879f550e9/profiles/da083d28-2bd3-4bdc-aabf-27f8d40f5c71/graph), [after](https://app.blackfire.io/envs/5f4f9a62-eaa0-45ee-b7b3-a1b879f550e9/profiles/fcb4eb61-f1c4-40df-93e0-0dc832d08611/graph), [comparison](https://app.blackfire.io/envs/5f4f9a62-eaa0-45ee-b7b3-a1b879f550e9/profiles/compare/da083d28-2bd3-4bdc-aabf-27f8d40f5c71...fcb4eb61-f1c4-40df-93e0-0dc832d08611/graph).

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
