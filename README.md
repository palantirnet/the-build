# "The" Build

Drupal build tools that we can use, reuse, and iterate on in our projects.

## Phing targets

This repository contains reusable phing build tasks for our Drupal projects, and in the future may contain custom phing build tasks.

To use these build tasks:

* Add `the-build` to your project using composer: `composer require palantirnet/the-build`
* Install the default `build.xml` to your project: `vendor/bin/phing -f vendor/palantirnet/the-build/tasks/install.xml`
* Edit your new build.xml to suit

## Our packages.json

This repository also contains a packages.json file listing our private `palantirnet` packages. This is published on the `gh-pages` branch so that we can reference a single source for our internal dependencies: [palantirnet.github.io/the-build/packages.json](https://palantirnet.github.io/the-build/packages.json)

* From the command line, you can reference this with composer's `--repository` flag.
* In a `composer.json` file, you can reference this in the `repositories` key:

```json
    "repositories": [
        {
            "type": "composer",
            "url": "https://palantirnet.github.io/the-build/packages.json"
        },
        {
            "type": "composer",
            "url": "https://packagist.drupal-composer.org"
        }
    ],

```
