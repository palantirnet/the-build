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

1. Properties are loaded from the phing call itself; for example, adding the flag `-Ddrupal.modules_enable=devel,kint` will set the `drupal.modules_enable` property to `devel,kint`
1. Set the `build.env` property (if it's not already set with `-D`) from the `PALANTIR_ENVIRONMENT` environment variable; if you're using [the-vagrant](https://github.com/palantirnet/the-vagrant), this will be set to `vagrant`
1. Load properties from your `conf/build.[environment].properties`
1. Load properties from `conf/build.default.properties`
1. Use default property values set in the phing targets

## Available properties
### Build

| Property | Default value | What is it? |
|---|---|---|
| `build.artifact_mode` | `symlink` | Whether to `symlink` or `copy` assets like CSS, JS, and other code during the build. |
| `build.test_output` | `/dev/null` | Where to output reports from tests. On Circle, try `${env.CIRCLE_TEST_REPORTS}`. |
| `build.drupal.settings` | `conf/drupal/settings.php` | Source template for Drupal's `settings.php` file. |
| `build.drupal.settings_dest` | `web/sites/default/settings.php` | Destination for the templated settings.php file. |
| `build.drupal.services` | `conf/drupal/services.yml` | Source template for Drupal's `services.yml` file. |
| `build.drupal.services_dest` | `web/sites/default/services.yml` | Destination for the templated `services.yml` file. |


### Drupal

| Property | Default value | What is it? |
|---|---|---|
| `drupal.site_name` | `Drupal` | Human-readable name for your site. |
| `drupal.profile` | `config_installer` | Install profile. |
| `drupal.modules_enable` | | Comma-separated list of modules to enable after installing Drupal. |
| `drupal.database.database` | `drupal` |  |
| `drupal.database.username` | `root` |  |
| `drupal.database.password` | `root` |  |
| `drupal.database.host` | `127.0.0.1` |  |
| `drupal.settings.file_public_path` | `sites/default/files` | Relative path to public files. |
| `drupal.settings.file_private_path` | `sites/default/private` | Relative path to private files. |
| `drupal.twig.debug` | | Whether to enable twig debugging. |
| `drupal.uri` | `http://drupal.local` | Your site's URI; the default may be the URI of your local development environment. |
| `drupal.hash_salt` | `temporary` | Salt for Drupal's password hashing. A unique salt is generated when you install `the-build`. |
| `drupal.root` | `web` | Relative path to the Drupal web root. This is co-dependent with the composer installer configuration in your `composer.json`. Changing this will probably cause problems. |
| `drupal.sites_subdir` | `default` | Name of the sites subdirectory where the `settings.php` file should live. |
| `drupal.admin_user` | `admin` | Drupal admin username, if you feel inclined to change it. |

[More info](../tasks/drupal.xml#L16-L38)

### Code Review

| Property | Default value | What is it? |
|---|---|---|
| `phpmd.rulesets` | `vendor/palantirnet/the-build/conf/phpmd.xml` | Relative path to the PHPMD configuration. |
| `drupal_code_sniffer.standard` | `vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml` | Relative path the the Drupal codesniffer standard. |
| `phptodo.config` | `conf/php_todo_finder.yml` | Relative path to the PHP To-do Finder configuration. |

[More info](code_review.md)

### Acquia

| Property | Default value | What is it? |
|---|---|---|
| `acquia.accountname` |  | Machine name of your Acquia site account. |
| `acquia.repo` |  | Acquia git repository, like `ACCOUNT@svn-6185.devcloud.hosting.acquia.com:ACCOUNT.git` |
| `acquia.branch` | `build` | Branch of the Acquia git repository where build artifacts should be committed. |
| `acquia.tag_prefix` | `release-` | String to use as a prefix on build tags; the repository tag `1.0.0` becomes the build tag `release-1.0.0`. |
| `acquia.dir` | `artifacts/acquia` | Relative path of where to keep the Acquia repository. |

[More info](../tasks/acquia.xml#L32-L59)

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
