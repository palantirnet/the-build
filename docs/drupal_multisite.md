# Using Drupal Multisites

Multisite builds are supported under `the-build`. When you have multiple sites configured in your `.the-build/build.default.properties.yml` file, and you run targets like `build` and `install` that act on a specific site, you will be prompted to select a site.

These site-specific targets declare a dependency on the `set-site` target in [tasks/the-build.xml](../tasks/the-build.xml), which sets up the site properties in `drupal.site.*`. This allows targets to reference the same properties (e.g. `${drupal.site.uri}`) for each site, rather than needing to reference a site-specific property (e.g. `${drupal.sites.default.uri}`, `${drupal.sites.intranet.uri}`).

Default values for all sites are set in the `drupal.sites._defaults.*` properties, but individual sites can override them.

Finally, site-specific directory properties are set dynamically based on `drupal.site.dir`, unless they're explicitly declared.

## Adding a new multisite

A target is provided for setting up new multisites:

```
$> phing drupal-add-multisite
```

This command will prompt you for the Drupal sites subdirectory and the site URL, and then will generate the required `settings.php` files and add the site to `sites.php`. It will output configuration values for you to paste into your `.the-build/build.default.properties.yml`.

You will still need to manually update your Vagrant, Behat, and CircleCI configuration.

## Example Multisite Configuration

```
drupal:
  root: web
  sites:
    default:
      dir: default
      uri: http://mysite.local
      database:
        database: drupal
    intranet:
      dir: intranet
      uri: http://intranet.mysite.local
    conference2019_mysite_com:
      dir: conference2019.mysite.com
      uri: http://conference2019.mysite.local
      admin_user: sarah
    _defaults:
      admin_user: obscure_admin_name
```

With this configuration:

* The `default` site uses the `drupal` database, and the other two sites use `intranet` and `conference2019_mysite_com`, respectively (set by `the-build.xml` based on the sites key)
* For `default` and `intranet`, user 1 is named `obscure_admin_name` (inherited from `drupal.sites._defaults`)
* For `conference2019_mysite_com` the admin is named `sarah` (overridden)
* These three sites are found in the directories `web/sites/default`, `web/sites/intranet`, and `web/sites/conference2019.mysite.com` respectively
        
      
