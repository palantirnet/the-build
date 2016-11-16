# "The" Build

_Note: If you are instantiating a project, you likely want to start with [drupal-skeleton](https://github.com/palantirnet/drupal-skeleton)._

This repository contains reusable phing build tasks for our Drupal projects.

## Adding the-build with composer

Before you can add `the-build` to your project, you need to it as a source in your `repositories` key:

```json
    "repositories": {
        "palantirnet/the-build": {
            "type": "vcs",
            "url": "git@github.com:palantirnet/the-build.git"
        }
    },
```

Then you can require the package:

```sh
$> composer require palantirnet/the-build
```

## Installing the tasks

Install the default `build.xml` to your project:

```sh
$> vendor/bin/phing -f vendor/palantirnet/the-build/tasks/install.xml
```

This will trigger an interactive prompt to configure your basic build properties, adding the following boilerplate files:

* `build.xml`
* `conf/build.default.properties`

Manually review these files, then check them in to your project. At this point, you'll generally fire up your Drupal site with:

```sh
$> vendor/bin/phing build install migrate
```
