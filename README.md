# symfony-real
Для проекта нужен docker (docker desktop) а так же docker-compose

## Сборка контейнеров и настройка проекта
Для настройки переменных окружения **docker** контейнеров необходимо скопировать файл и внести собственные настройки
```shell
cp .env-example .env
```
Для настройки переменных окружения **symfony** прокта необходимо скопировать файл и внести собственные настройки для проекта
```shell
cp app/.env-example app/.env
```
собираем контенеры и стратуем docker
```shell
docker-compose build &&  docker-compose up -d
```
или если контейнеры уже были собраны то запусить контейнеры командой
```shell
docker-compose up -d
```

Собрать фронт часть проекта:
```shell
docker-compose run --rm front sh -c "yarn install && yarn encore production"
```
Для настройки symfony проекта можно зайти в контейнер php и выполняем настройку
```shell
docker-compose exec php bash
```
Находясь в контейнере _php_ нужно выполнить настройку symfony проекта (мы находимся в контейнере _php_): 
в появившейся командной строке 
```shell
composer install && bin/console doctrine:migrations:migrate && bin/console doctrine:fixtures:load -q   
```
🏃🏻 ‍Либо можно выполнить через docker-compose команду  
```shell
docker-compose run --rm php sh -c \
"composer install\
&& php bin/console doctrine:migrations:migrate -n\
&& php bin/console doctrine:fixtures:load -n"
```

#### В разработке проекта
Для выполнения задач связанных с разработкой и настройкой symfony проекта можно заходить в контейнер _php_ и выполнять привычные команды для symfony: 
```shell
docker-compose exec php bash
```
Появится командная строка интерпретатора **bash** запущенного внутри контейнера. Например, посмотреть существующие роуты в symfony проекте:
```shell
bin/console debug:route
```

🚀 Проект можно по адресу `http://localhost` в браузере

### Фронт часть проекта
Фронт-часть проекта развернута в контейнере `front`

Для сборки фронт части в продекшен режиме выполнить команду 
```shell
docker-compose run --rm front sh -c "yarn encore production"
```
Для сборки фронт-части в dev режиме
```shell
docker-compose run --rm front sh -c "yarn encore dev"
```
в режиме наблюдения (пересобирает на лету)
```shell
docker-compose run --rm front sh -c "yarn encore dev --watch"
```

ok.
