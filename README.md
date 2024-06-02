# "The" Build

This repository contains project setup templates and reusable phing build targets for Drupal projects.

_Note: If you are starting a new project, you may be looking for the [drupal-skeleton](https://github.com/palantirnet/drupal-skeleton)._

## Adding the-build with composer

```sh
$> composer require palantirnet/the-build
```

Composer 2.2.2 or greater is required for the-build.

## Setting up

Install the default templates and phing targets to your project:

```sh
$> vendor/bin/the-build-installer
```

This will trigger an interactive prompt to configure your basic build properties, adding the following templated files and directories:

* `.circleci/`
* `.the-build/`
* `behat.yml`
* `build.xml`
* `drush/drushrc.php`
* `drush/*.aliases.drushrc.php`
* `config/`
* `(web|docroot)/sites/default/settings.php`
* `(web|docroot)/sites/default/settings.(host).php`

These files should be checked in to your project.

Configure your build by editing `.the-build/build.yml`. You can find more properties in [defaults.yml](defaults.yml), and override the defaults by copying them into your project's properties files.

## Using the-build
### Everyday commands

Reinstall the Drupal site from config:

```sh
$> vendor/bin/phing install
```

Rebuild the `settings.build.php` configuration, and the styleguide if it's available (run automatically when you call `install`):

```sh
$> vendor/bin/phing build
```

Run code reviews and tests:

```sh
$> vendor/bin/phing test
```

### Other commands

View a list of other available targets with:

```sh
$> vendor/bin/phing -l
```

## Additional documentation

* [Configuring the-build](docs/configuration.md)
* [Building an artifact](docs/artifacts.md)
* [Using Drupal multisites](docs/drupal_multisite.md)
* [Custom Phing tasks provided by the-build](docs/tasks.md)
* [Developing on the-build](docs/development.md)

----
Copyright 2016-2020 Palantir.net, Inc.
