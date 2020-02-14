# Composer Vendor Cleaner [![Packagist](https://img.shields.io/packagist/v/liborm85/composer-vendor-cleaner.svg)](https://packagist.org/packages/liborm85/composer-vendor-cleaner)

This composer plugin removes unnecessary development files and directories from `vendor` directory.

## Installation

Local installation to project:
```
composer require liborm85/composer-vendor-cleaner
```

Global installation:
```
composer global require liborm85/composer-vendor-cleaner
```

## Configuration

Develompent files and directories to remove can be defined in `composer.json` file
in [`extra`](https://getcomposer.org/doc/04-schema.md#extra) data attribute per new key `dev-files`.
Glob pattern syntax is fully supported.

Example:
```
"extra": {
    "dev-files": {
        "/": [                  // shortcut for "*/*", means: find in all library packages directories
            "tests/",           // means: tests directory whatever
            "docs/",
            ".travis.yml"       // means: .travis.yml file whatever
        ],
        "twig/twig": [          // means: find only in twig/twig package directory
            "doc/"
            "/drupal_test.sh"   // means: only file in root directory of twig/twig package
        ],
        "symfony/*": [          // means: find in all symfony packages
            "Tests/"
        ],
        "other/package": [
            "/src/**/*.md"      // means: find whatever all md files in src directory, eg.: /src/dir1/test.md, /src/dir1/dir2/readme.md
        ]
    }
}
```

For additional configuration can be used [`config`](https://getcomposer.org/doc/04-schema.md#config) attribute.

- `match-case` _(default: true)_ - Match case of name files and directories.

Example:
```
"config": {
    "dev-files": {
        "match-case": false
    }
}
```

## Why a new plugin?

Some composer packages contain files and directories that do not belong to production servers, but composer
does not solve this.

Exists a lot of plugins for composer trying to solve this issue, they don't have advanced patterns to filtering
or they are not user definable. Or some have no configuration and it works automatically and delete almost everything
and then the package does not work.

That's why I created a new one that allows advanced filtering by glob patterns.

If you miss a feature or find bug, please, create an [issue](https://github.com/liborm85/composer-vendor-cleaner/issues).

## License

MIT
