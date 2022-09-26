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

## Requirements

- PHP 5.6.0+

## Configuration

Development files and directories to remove can be defined in `composer.json` file
in [`extra`](https://getcomposer.org/doc/04-schema.md#extra) data attribute per new key `dev-files`.
Glob pattern syntax is fully supported.

Example:
```
"extra": {
    "dev-files": {
        "/": [                  // means: find in all library packages directories and bin directory
            "tests/",           // means: tests directory whatever
            "docs/",
            ".travis.yml"       // means: .travis.yml file whatever
        ],
        "*/*": [                // means: find in all library packages directories, but NOT in bin directory
            "*.sh"
        ],
        "bin": [                // means: find in composer bin directory
            "*.bat"
        ],
        "twig/twig": [          // means: find only in twig/twig package directory
            "doc/",
            "/drupal_test.sh"   // means: only file in root directory of twig/twig package
        ],
        "symfony/*": [          // means: find in all symfony packages
            "Tests/"
        ],
        "other/package": [
            "/src/**/*.md"      // means: find whatever all md files in src directory, eg.: /src/dir1/test.md, /src/dir1/dir2/readme.md
        ],
        "example/package": [    // means: remove all files and directories in language directory without cs.php file
            "languages/*",
            "!languages/cs.php" // means: exclude cs.php file from remove
        ]
    }
}
```

Development files and directories can also be defined in an external json file, by specifying the relative path to
this file in the `dev-files` key in the root composer.json. The format is the same as above.
```
"extra": {
    "dev-files": "composer.dev-files.json"
}
```

Example of `composer.dev-files.json` file:
```
{
  "/": [
    ".github/"
  ],
  "twig/twig": [
    "doc/"
  ]
}
```

For additional configuration can be used [`config`](https://getcomposer.org/doc/04-schema.md#config) attribute.

- `match-case` _(default: `true`)_ - Match case of name files and directories.
- `remove-empty-dirs` _(default: `true`)_ - Removes empty directories.
- `no-dev-only` _(default: `false`)_ - If is set `true` start the cleanup only if the composer command is run with `--no-dev`.

Example:
```
"config": {
    "dev-files": {
        "match-case": false,
        "remove-empty-dirs": false
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
