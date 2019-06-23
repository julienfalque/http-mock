vendor:
	composer install

.PHONY: tests
tests: phpunit

phpunit: vendor
	vendor/bin/phpunit

php-cs-fixer: tools/php-cs-fixer/vendor
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -vv --show-progress estimating-max --diff-format udiff

php-cs-fixer-dry-run: tools/php-cs-fixer/vendor
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -vv --show-progress estimating-max --diff-format udiff --dry-run

tools/php-cs-fixer/vendor: tools/php-cs-fixer/composer.lock
	composer install --working-dir tools/php-cs-fixer
