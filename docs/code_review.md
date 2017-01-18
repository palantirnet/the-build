# Code Review in the-build

From your project where you have installed the-build, run `vendor/bin/phing code-review` to run the default set of code reviews. This target is provided in the default `build.xml` created for your project during install.

This will review your code with:

* Drupal Codesniffer
* PHPmd
* PHP Lint
* PHPtodo

Generally, you should configure your `build.xml` to run code reviews as part of your `test` target so that developers run the reviews by default.

### PHP Lint

PHP Lint uses the PHP interpreter directly to check for syntax errors. There is no configuration for this review.

### [PHPMD](https://phpmd.org/)

A more complicated and more general PHP code review than the Drupal Codesniffer standard. The default config for this review can be found within the-build at `conf/phpmd.xml`. To customize this config, copy that file to your project's `conf/` directory and add the build property:

```
phpmd.rulesets=conf/phpmd.xml
```

### Drupal Codesniffer

Runs codesniffer using the standard provided by Drupal's [Coder](https://www.drupal.org/project/coder) module. Generally, you should not change the configuration for this review, but if do need to you can provide a different standard:

```
drupal_code_sniffer.standard=vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml
```

You can also change the set of files that the sniff reviews:

```
# Default
drupal_code_sniffer.file=vendor/palantirnet/the-build/conf/drupal_code_sniffer_files_d8.txt

# Drupal 7 code
vendor/palantirnet/the-build/conf/drupal_code_sniffer_files_d7.txt

# Your own list of files
conf/drupal_code_sniffer_files_myproject.txt
```

By default, when Codesniffer finds problems it will throw an error. If you must, you can quiet this by setting this property:

```
drupal_code_sniffer.haltonerror=false
```

### [PHP To-do Finder](https://github.com/nilportugues/php-todo-finder)

Sets a threshold for the number of "to do" comments allowable in a codebase. The default config for this review can be found within the-build at `conf/php_todo_finder.yml`. To customize this config, copy that file to your project's `conf/` directory and add the build property:

```
phptodo.config=conf/php_todo_finder.yml
```

----
Copyright 2016 Palantir.net, Inc.
