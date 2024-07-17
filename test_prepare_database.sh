bin/console doctrine:schema:drop -e test --force && bin/console doctrine:schema:create -e test
bin/console doctrine:fixtures:load -e test
