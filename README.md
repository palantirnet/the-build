# "The" Build

_Note: If you are instantiating a project, you likely want to start with [drupal-skeleton](https://github.com/palantirnet/drupal-skeleton)._

Drupal build tools that we can use, reuse, and iterate on in our projects.

## Phing targets

This repository contains reusable phing build tasks for our Drupal projects, and in the future may contain custom phing build tasks.

To use these build tasks:

* Add `the-build` to your project using composer: `composer require palantirnet/the-build`
* Install the default `build.xml` to your project: `vendor/bin/phing -f vendor/palantirnet/the-build/tasks/install.xml`
* Edit your new `build.xml` and `conf/build.default.properties` to suit

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

# Troubleshooting

If you get errors after building that indicate the site was installed successfully, but that the system was unable to send an email (updating you about core updates, or anything else), then you may have to update your exported `update.settings.yml` config file to remove the "email" notification. You can [see an example here](https://gist.github.com/lukewertz/70a63df9c0e5a7c1252e6547e701c69b).
