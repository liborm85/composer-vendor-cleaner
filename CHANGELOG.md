# Changelog

## 1.7.1 - 2023-06-02

- fixed behavior if package install path is null

## 1.7.0 - 2022-07-29

- `dev-files` settings support via external JSON file

## 1.6.0 - 2022-01-01

- detection for unused patters (`dev-files`)

## 1.5.0 - 2020-11-18

- new `no-dev-only` setting for disable cleanup in dev mode (default: `false`)

## 1.4.0 - 2020-11-15

- new glob exclude pattern `!`
- fixed cleaning in removed packages by other composer plugin (e.g. in Drupal)

## 1.3.1 - 2020-05-15

- fixed removing files on Windows

## 1.3.0 - 2020-05-11

- support for Composer 2.0

## 1.2.2 - 2020-04-06

- fixed removing invalid symlinks

## 1.2.1 - 2020-03-20

- fixed displaying the cleanup result when using a parameter `--no-autoloader`

## 1.2.0 - 2020-03-15

- feature for cleanup in composer `bin` directory
- feature for removes empty directories

## 1.1.2 - 2020-02-14

- fixed merging multiple patters for cleaning

## 1.1.1 - 2020-02-14

- refactored packages processing and cleaning
- fixed sorting directories for remove (only some versions of PHP)

## 1.1.0 - 2019-11-20

- new `match-case` setting
- cleanup event fired even if skips autoloader generation (composer option `--no-autoloader`)

## 1.0.0 - 2019-10-27

- initial release
