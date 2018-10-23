# Custom Tasks

## IncludeResourceTask [🔗](../src/TheBuild/IncludeResourceTask.php)

Copies or symlinks a file or directory within your project.

### Attributes

| Name | Type | Description | Default | Required |
|---|---|---|---|---|
| mode | string | Either `copy` or `symlink`. Inherited from `includeresource.mode` if available. | `symlink` | No |
| source | string | Path to the resource. | n/a | Yes |
| dest | string | Path where the resource should be included in your build. | n/a | Yes |

### Examples

```xml
<!-- Symlink generated CSS into your theme. -->
<includeresource source="styleguide/src/content/assets/css" dest="web/themes/custom/my_theme/css" />

<!-- Use build properties to include a JS library in your Drupal libraries directory -->
<property name="includeresource.mode" value="copy" />
<includeresource source="vendor/foo/somelibrary" dest="${drupal.root}/libraries/somelibrary" />
```

## CopyPropertiesTask [🔗](../src/TheBuild/CopyPropertiesTask.php)

Copies phing properties matching a prefix to properties with a different prefix.

### Attributes

| Name | Type | Description | Default | Required |
|---|---|---|---|---|
| fromPrefix | string | Prefix for source properties. | | Yes |
| toPrefix | string | New prefix for matching properties. | | Yes |
| override | boolean | Whether to overwrite existing property values. | true | No |

If either of the `fromPrefix` or `toPrefix` attributes does not end in a `.`, one will be added.

### Example

```xml
<!-- Copy Drupal multisite properties into a consistent property location. -->
<copyproperties fromPrefix="drupal.sites.default" toPrefix="drupal.site" override="true" />
```

## ForeachKeyTask [🔗](../src/TheBuild/ForeachKeyTask.php)

Iterate over Phing property values.

### Attributes

| Name | Type | Description | Default | Required |
|---|---|---|---|---|
| prefix | string | Prefix for properties to iterate over. | | Yes |
| target | string | Phing target to run for each property. | | Yes |
| keyParam | string | Name of a target parameter to pass the property key in. | | Yes |
| prefixParam | string | Name of a target parameter to pass the property prefix in. | | Yes |
| omitKeys | string | Comma-separated list of keys to ignore. | | false |

If the `prefix` attribute does not end in a `.`, one will be added.

### Example

```xml
<foreachkey prefix="drupal.sites" omitKeys="_defaults" target="mytarget" keyParam="key" prefixParam="prefix" />
```

## SelectPropertyKeyTask [🔗](../src/TheBuild/SelectPropertyKeyTask.php)

Interactively select a key from available property keys.

* If the propertyName property is already set, the task does nothing
* If no keys are available, the propertyName property is not set and the task does nothing
* If there is only one key available, that key is used and the user is not prompted
* If there are multiple keys available, the user will be prompted to select one using a multiple choice menu



### Attributes

| Name | Type | Description | Default | Required |
|---|---|---|---|---|
| prefix | string | Prefix for properties to select among. | | Yes |
| propertyName | string | Property to populate with the selected value. | | Yes |
| message | string | Prompt to display to the user. | `Select one:` | Yes |
| omitKeys | string | Comma-separated list of keys to ignore. | | false |

If the `prefix` attribute does not end in a `.`, one will be added.

### Example

```xml
<selectpropertykey prefix="drupal.sites." omitKeys="_defaults" propertyName="build.site" message="Select a site to build:" />
```

## Acquia\GetLatestBackupTask [🔗](../src/TheBuild/Acquia/GetLatestBackupTask.php)

Download a recent backup from Acquia Cloud.

### Attributes

| Name | Type | Description | Default | Required |
|---|---|---|---|---|
| dir | directory path | Local backups directory. | | Yes |
| realm | string | Acquia hosting realm, either "devcloud" or "prod". | | Yes |
| site | string | Acquia site name. | | Yes |
| env | string | Acquia environment, generally "dev", "test", or "prod". | | Yes |
| database | string | Acquia database name. | The site name. | No |
| maxAge | int | Maximum age of the backup, in hours. | 24 | No |
| propertyName | string | Name of a property to set to the backup file. | | No |
| credentialsFile | file path | Path to your Acquia Cloud API credentials. (Do not check this file in to your repository) | `~/.acquia/cloudapi.conf` | No |

### Example

```xml
<!-- Provide the <getAcquiaBackup /> task. -->
<taskdef name="getAcquiaBackup" classname="TheBuild\Acquia\GetLatestBackupTask" />

<!-- Required parameters only -->
<getAcquiaBackup dir="artifacts/backups" realm="devcloud" site="mysite" env="prod" />

<!-- More parameters -->
<getAcquiaBackup dir="artifacts/backups" realm="devcloud" site="mysite" env="prod" credentialsFile="artifacts/.acquia/cloudapi.conf" propertyName="drupal.site.load_db.file" />
```
