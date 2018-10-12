# Configuring the-build

## Where to set configuration

In general, you should configure the-build for your project in the `.the-build/build.default.properties.yml` file within your project root.

You can customize your test and prod build behaviors by adding environment-specific configuration to the `.the-build/build.ENVIRONMENT.properties.yml` files.

You can find documentation for the full set of properties used by the-build in the [`defaults.properties.yml`](../defaults.properties.yml) file. Properties and default values can be copied from there into your project's `.the-build/build.default.properties.yml` file.

## Property loading

In Phing, properties use the first value you assign to them rather than the last. For example:

```xml
<property name="myProp" value="green" />
<property name="myProp" value="blue" />
<echo>${myProp}</echo>

Yields:
     [echo] green


<property name="anotherProp" value="red" />
<property name="anotherProp" value="orange" override="true" />
<echo>${anotherProp}</echo>

Yields:
     [echo] orange
```

Cool! This phing-ism is what allows us to do environment-specific property layering and loading. The full property load order follows this pattern:

1. Properties are loaded from flags on the phing call itself; for example:

  ```
  phing build -Dbuild.env=circleci
  ```
1. Set the `build.dir`, `projectname`, `build.thebuild.dir`, and `build.env` core properties in `tasks/the-build.xml`
1. Load properties from the project's `.the-build/build.[environment].properties.yml`
1. Load properties from the project's `.the-build/build.default.properties.yml`
1. Load default property values from the-build's own `defaults.properties.yml` file

In order to support Drupal multisites, site-specific configuration should be set in the `drupal.sites.SITENAME.*` properties, but should be referenced using the `drupal.site.*` properties. See [drupal_multisite.md](drupal_multisite.md) for details on how these properties are provided.

## Build environments

Generally, the `build.env` property is set from the `PALANTIR_ENVIRONMENT` environment variable.

* [the-vagrant](https://github.com/palantirnet/the-vagrant) provides this variable as `vagrant`
* The `.circleci/config.yml` template included with the-build will set this to `circleci`
* In specific circumstances, like testing build configuration for other environments, it can make sense to set the `build.env` variable via a runtime flag:

  ```
  phing build -Dbuild.env=circleci
  ```

## Core properties

There are four core properties that are always available in the-build:

| Property | Description |
|---|---|
| `build.dir` | The project directory. This is set from Phing's [built in application.startdir property](https://www.phing.info/phing/guide/en/output/hlhtml/#sec.builtinprops). |
| `projectname` | A machine name for the project. Set based on the `build.dir`, with the `.local` suffix removed (if it's present). |
| `build.thebuild.dir` | Path to `the-build` code. Used to find and load default properties and templates. |
| `build.env` | The build environment. Used for loading environment-specific properties files, and set from the `PALANTIR_ENVIRONMENT` environment variable. |
| `build.site` | The multisite to build; only used when running site-specific targets. Can be set from the `THE_BUILD_ENVIRONMENT` variable. See [drupal_multisite.md](drupal_multisite.md). |

These properties are provided by the init process in `the-build.xml`, and do not need to be set or overridden in your local build configuration.

## Available properties

All available properties are documented in the-build's [defaults.properties.yml](../defaults.properties.yml) file. This file is also the source of default values for the-build; anything that is not set in your project's `.the-build/build.*.properties.yml` files will be set based on these defaults.

----
Copyright 2016, 2017, 2018 Palantir.net, Inc.
