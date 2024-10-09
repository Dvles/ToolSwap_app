# chmod +x fixturefull.sh ---> give access on mac


php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load --no-interaction