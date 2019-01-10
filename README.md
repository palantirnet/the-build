# "The" Build

This repository contains project setup templates and reusable phing build tasks for Drupal projects.

_Note: If you are starting a new a project, you may be looking for the [drupal-skeleton](https://github.com/palantirnet/drupal-skeleton)._

## Adding the-build with composer

```sh
$> composer require palantirnet/the-build
```

## Installing the templates

Install the default `build.xml` to your project:

```sh
$> vendor/bin/the-build-installer
```

This will trigger an interactive prompt to configure your basic build properties, adding the following templated files:

* `.circleci/`
* `.gitignore`
* `.the-build/`
* `behat.yml`
* `build.xml`
* `drush/drushrc.php`
* `config/` (if you elect to install Drupal)

These files should be checked in to your project. At this point, you'll generally fire up your Drupal site with:

```sh
$> vendor/bin/phing build install
```

And you'll test your site with:

```sh
$> vendor/bin/phing test
```

## Configuration

Configure your build by editing `.the-build/build.yml`. You can find more properties in [defaults.yml](defaults.yml), and override the defaults by copying them into your project's properties files.

## Additional documentation

* [Configuring the-build](docs/configuration.md)
* [Building an artifact](docs/artifacts.md)
* [Using Drupal multisites](docs/drupal_multisite.md)
* [Custom tasks provided by the-build](docs/tasks.md)
* [Developing on the-build](docs/development.md)

----
Copyright 2016, 2017, 2018, 2019 Palantir.net, Inc.
