# Configuring the-build

Unlike other languages, in phing, properties use the first value you assign to them, rather than the last. For example:

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

Cool! This phing-ism is what powers our environment-specific property layering and loading:

1. Properties are loaded from the phing call itself; for example, adding the flag `-Dbuild.test_output=artifacts/tests` will set the `build.test_output` property to `artifacts/tests`
1. Set the `build.env` property (if it's not already set with `-D`) from the `PALANTIR_ENVIRONMENT` environment variable; if you're using [the-vagrant](https://github.com/palantirnet/the-vagrant), this will be set to `vagrant`
1. Load properties from your `conf/build.[environment].properties`
1. Load properties from `conf/build.default.properties`
1. Use default property values set in the phing targets

## Available properties
### Build

| Property | Default value | What is it? |
|---|---|---|
| `build.test_output` | `/dev/null` | Where to output reports from tests. On Circle, try `${env.CIRCLE_TEST_REPORTS}`. |
| `build.drupal.settings` | `conf/drupal/settings.php` | Source template for Drupal's `settings.php` file. |
| `build.drupal.settings_dest` | `web/sites/default/settings.php` | Destination for the templated settings.php file. |
| `build.drupal.services` | `conf/drupal/services.yml` | Source template for Drupal's `services.yml` file. |
| `build.drupal.services_dest` | `web/sites/default/services.yml` | Destination for the templated `services.yml` file. |


### Drupal

* [Configuration options](../defaults.properties.yml)

### Code Review

#### PHP Linting

Use the PHP interpreter's built in linter to check for syntax errors and deprecated code.

| Property | Default value | What is it? |
|---|---|---|
| `phplint.includesfile`    | `${build.thebuild.dir}/conf/phplint.txt` | Path to a file where each line is a pattern matching files to lint. |

#### PHP_CodeSniffer

Use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) to review code according to the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

| Property | Default value | What is it? |
|---|---|---|
| `phpcs.standard`    | `${build.dir}/vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml` | Path to a PHP_CodeSniffer standard file. |
| `phpcs.directories` | `${drupal.root}/modules/custom ${drupal.root}/themes/custom` | Space-separated list of directories to review. |
| `phpcs.ignore`      | `*.md` | Comma-separated list of patterns for files and directories to exclude from the PHP_CodeSniffer review. |

#### PHPmd

Use [PHP Mess Detector](https://phpmd.org/) to check for general PHP best practices, unused variables, and complexity.

| Property | Default value | What is it? |
|---|---|---|
| `phpmd.rulesets`    | `${build.thebuild.dir}/conf/phpmd.xml` | Comma-separated list of ruleset paths or names. |
| `phpmd.directories` | `${drupal.root}/modules/custom/,${drupal.root}/themes/custom/` | Directories to review. |
| `phpmd.suffixes`    | `php,inc,module,install,profile` | File extensions to review. |
| `phpmd.format`      | `text` | Output format for phpmd. |

### Artifacts

* [Artifact build process](artifacts.md)
* [Configuration options](../defaults.properties.yml)

#### Minimum configuration

In order to use the artifact build, you must set the `artifact.git.remote` property in your project's  file to the git repository for the artifact. This is typically an Acquia or Pantheon git URL:

```
artifact:
  git:
    remote: example@svn-9999.devcloud.hosting.acquia.com:example.git
```

All artifact configuration should be in your project's base properties file, `.the-build/build.default.properties.yml`.

#### Runtime flags

* `push` - Value should be `y` or `n`. When this flag is provided, it will bypass the "Push artifact changes?" prompt.

```
$> phing artifact -Dpush=y
```

#### Example: Pushing an artifact to an Acquia environment

1. Configure the artifact in the `conf/build.default.properties` file of your project:

  ```
  # Acquia git URL
  artifact.git.remote=example@svn-9999.devcloud.hosting.acquia.com:example.git

  # The Acquia web root must be 'docroot'
  # This must also be configured in your composer.json, and reflected in your repository
  drupal.root=docroot
  ```
2. Build the artifact by running this command:

  ```
  $> phing artifact
  ```

#### Example: Pushing an artifact to a Pantheon environment

1. Configure the artifact in the `conf/build.default.properties` file of your project:

  ```
  # Pantheon git URL
  artifact.git.remote=ssh://codeserver.dev.*@codeserver.dev.*.drush.in:2222/~/repository.git

  # All artifacts go to the master branch
  artifact.git.remote_branch=master
  ```
2. Build the artifact by running this command:

  ```
  $> phing artifact
  ```
3. Build to a Pantheon multidev environment (multidev branch names must be 11 characters or shorter):

  ```
  $> phing artifact -Dartifact.git.remote_branch=TICKET-999
  ```

Alternatively, you may chose to not set the `artifact.git.remote_branch` property, and instead, and then merge the default artifact branch (generally `artifact-develop`) to `master` within the Pantheon UI.

### Acquia

* [Configuration options](../defaults.properties.yml)

### DB Loading

| Property | Default value | What is it? |
|---|---|---|
| `db.load.export_pattern` | `artifacts/*` | Pattern to match gzipped database dump files. |
| `db.load.mysql_command` | `drush sqlc` | Command with which to load stuff into Drupal. |
| `db.load.file` |  | Load a specific file rather than one matching the `export_pattern`. |

Example usage:

```
    <import file="vendor/palantirnet/the-build/tasks/lib/db.xml" />

    <target name="load">
        <phingcall target="load-db">
            <property name="db.load.export_prefix" value="artifacts/prod-*" />
        </phingcall>
    </target>
```

[More info](../tasks/lib/db.xml)

----
Copyright 2016 Palantir.net, Inc.
