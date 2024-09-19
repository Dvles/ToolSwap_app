@REM effacer les fichiers de migration
del migrations\V*
@REM effacer la BD
symfony console doctrine:database:drop --force --no-interaction
@REM créer la BD
symfony console doctrine:database:create
@REM créer une migration
symfony console make:migration --no-interaction
@REM lancer la migration
symfony console doctrine:migrations:migrate --no-interaction
@REM lancer fixtures -- symfony console doctrine:fixtures:load