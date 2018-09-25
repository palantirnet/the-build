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
