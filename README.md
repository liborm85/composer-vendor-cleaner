# Composer Vendor Cleaner [![Packagist](https://img.shields.io/packagist/v/liborm85/composer-vendor-cleaner.svg)](https://packagist.org/packages/liborm85/composer-vendor-cleaner)

This composer plugin removes unnecessary development files and directories from `vendor` directory.

## Installation

Local installation to project:
```
composer require liborm85/composer-vendor-cleaner
```

Global instlallation:
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
        "/": [                  // means: find in all library packages directories
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

## License

MIT
