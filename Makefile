default: composer
	vendor/bin/php-cs-fixer fix
	vendor/bin/psalm

composer:
	composer install --no-interaction --no-progress --optimize-autoloader --quiet
