init : 
	docker-compose up --build
up :
	docker-compose up -d
down :
	docker-compose down
ps :
	docker-compose ps
test :
	docker exec saule_php-fpm_1 php /var/www/html/tests/run.php

clean :
	docker-compose down --remove-orphans
