#!/bin/bash

# Effacer les fichiers de migration
rm -f migrations/V*

# Effacer la BD
symfony console doctrine:database:drop --force --no-interaction

# Créer la BD
symfony console doctrine:database:create

# Créer une migration
symfony console make:migration --no-interaction

# Lancer la migration
symfony console doctrine:migrations:migrate --no-interaction

# Lancer fixtures
symfony console doctrine:fixtures:load --no-interaction
