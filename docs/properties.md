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

[More info](code_review.md)

### Artifacts

For additional documentation on the artifact build process, see [docs/artifac

| Property | Default value | What is it? |
|---|---|---|
| `artifact.git.remote` | **(required)** | Git repository for the artifact. This is typically an Acquia or Pantheon git URL. |
| `artifact.git.remote_branch` | `${artifact.prefix}` plus the current branch name | Name of the remote branch to push the artifact to. For Acquia, the default is good because it shouldn't match a branch names on the development repository; on Pantheon, code must be on the `master` branch to be deployed to the `live` environment. |
| `artifact.directory` | `artifacts/build` | The path of the working directory where the artifact should be built. |
| `artifact.prefix` | `artifact-` | A string prefix to use for branch names. |
| `artifact.gitignore_template` | `vendor/palantirnet/the-build/conf/artifact-gitignore` | Path to a template .gitignore file to use in the artifact. |
| `artifact.readme_template` | `vendor/palantirnet/the-build/conf/artifact-gitignore` | Path to a template README file to use in the artifact. |
| `artifact.git.remote_base_branch` | `master` | Name of a branch to use as the base, if the artifact.remote_branch does not yet exist on the artifact.git.remote repository. |
| `artifact.git.remote_name` | `origin` | Name to use for the git remote on the artifact repository. |

#### Runtime flags

* `push` - Value should be `y` or `n`. When this flag is provided, it will bypass the "Push artifact changes?" prompt.

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
  phing artifact
  ```
3. Build to a Pantheon multidev environment (multidev branch names must be 11 characters or shorter):

  ```
  phing artifact -Dartifact.git.remote_branch=TICKET-999
  ```

Alternatively, you may chose to not set the `artifact.git.remote_branch` property, and instead, and then merge the default artifact branch (generally `artifact-develop`) to `master` within the Pantheon UI.

### Acquia

| Property | Default value | What is it? |
|---|---|---|
| `acquia.accountname` |  | Machine name of your Acquia site account. |
| `acquia.ssh` |  | Acquia SSH host, like `srv-1234.devcloud.hosting.acquia.com` or `staging-12345.prod.hosting.acquia.com` |

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
