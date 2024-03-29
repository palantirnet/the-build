# Developing on the-build

To develop and test changes to the-build, you'll generally need to have a Drupal codebase to run the-build on.

1. Choose a site to test against
2. Check out the-build as a git repository into the site's `vendor/` directory
3. Make, commit, and push your changes from within `vendor/palantirnet/the-build/`

The major thing to watch out for here is that your copy of the-build is temporary -- in certain cases (especially when switching branches) when you run composer commands, your repo may be replaced with a different version.

## Running code reviews on the-build

Our build tool should follow the same quality standards as our projects.

We run automated tests using PHP Code Beautifier, PHP Code Sniffer, PHP MD, and PHPStan. You can invoke all of these with a single command from the project root:

`./code-review.sh`

These tests -- except for PHP Code Beautifier -- also run on CircleCI.

## Testing on an existing site

You can clone the-build into the vendor directory of an existing site. If it doesn't have a version of the-build already installed, you'll need to require it with `composer require palantirnet/the-build:dev-release-2.0 --prefer-source --dev` first.

```
cd my-site
composer require palantirnet/the-build:dev-release-2.0 --prefer-source --dev
```

At this point, `vendor/palantirnet/the-build` should be a clone of `git@github.com:palantirnet/the-build.git`.

## Creating a new site for testing

Follow the [Quick Start guide for palantirnet/drupal-skeleton](https://github.com/palantirnet/drupal-skeleton#quick-start). Before installing the-build in step 3, update the required version of the-build:

```
composer require palantirnet/the-build:dev-release-2.0 --prefer-source
```

----
Copyright 2018-2022 Palantir.net, Inc.
