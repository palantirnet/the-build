#!/bin/bash

# auto-fix what can be fixed
echo "Running PHPCBF"
echo "--------------"
vendor/bin/phpcbf src --standard="Drupal,DrupalPractice" -n --extensions="php,module,inc,install,test,profile,theme"
# find what is left to fix
echo "Running PHPCS"
echo "-------------"
vendor/bin/phpcs src --standard="Drupal,DrupalPractice" -n --extensions="php,module,inc,install,test,profile,theme"
echo "Running PHPMD"
echo "-------------"
vendor/bin/phpmd src ansi defaults/standard/phpmd.xml "php,inc,module,theme,profile,install,test"
echo "Running PHPStan"
echo "---------------"
vendor/bin/phpstan analyse src --level=2
