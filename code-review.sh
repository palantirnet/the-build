#!/bin/bash

echo "Running PHPCBF on modules"
echo "--------------"
vendor/bin/phpcbf defaults/standard/modules --standard="Drupal,DrupalPractice" -n --extensions="php,module,inc,install,test,profile,theme"
# find what is left to fix
echo "Running PHPCS on modules"
echo "-------------"
vendor/bin/phpcs defaults/standard/modules --standard="Drupal,DrupalPractice" -n --extensions="php,module,inc,install,test,profile,theme"
echo "Running PHPMD on modules"
echo "-------------"
vendor/bin/phpmd defaults/standard/modules text defaults/standard/phpmd.xml --suffixes php,inc,module,theme,profile,install,test
echo "Running PHPStan on modules"
echo "---------------"
vendor/bin/phpstan analyse defaults/standard/modules --level=2

# auto-fix what can be fixed
echo "Running PHPCBF on tasks"
echo "--------------"
vendor/bin/phpcbf src --standard="Drupal,DrupalPractice" -n --extensions="php,module,inc,install,test,profile,theme"
# find what is left to fix
echo "Running PHPCS on tasks"
echo "-------------"
vendor/bin/phpcs src --standard="Drupal,DrupalPractice" -n --extensions="php,module,inc,install,test,profile,theme"
echo "Running PHPMD on tasks"
echo "-------------"
vendor/bin/phpmd src text defaults/standard/phpmd.xml --suffixes php,inc,module,theme,profile,install,test
echo "Running PHPStan on tasks"
echo "---------------"
vendor/bin/phpstan analyse src --level=2
