# Changelog

## Unreleased

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
