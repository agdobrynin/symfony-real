# symfony-real
Для проекта нужен docker (docker desktop) а так же docker-compose

## Сборка контейнеров и настройка проекта

Для настройки переменных окружения прокта необходимо скопировать файл из `.env-exmple` в `.env` и внести собственные настройки для проекта
```shell
cp .env-example .env
```
собираем контенеры и стратуем docker
```shell
docker-compose build &&  docker-compose up -d
```
собираем фронт часть проекта
```shell
docker-compose run --rm front sh -c "yarn install && yarn encore production"
```
для настройки symfony проекта заходим в контецнер php и выполняем настройку
```shell
docker-compose exec php bash
```
в появившейся командной строке (мы находимся в контейнере)
```shell
composer install  
```
оставая в контецнере выполним миграции и заполним тестовыми данными базу
```shell
bin/console doctrine:migrations:migrate && bin/console doctrine:fixtures:load -q
```
посмотреть существующие роуты проекта можно в контейнере `php`
```shell
bin/console debug:route
```
открыть проект можно по адресу `localhost` в браузере

### Фронт часть проекта
Фронт-часть проекта развернута в контейнере `front`

Для сборки фронт части в продекшен режиме выполнить команду 
```shell
docker-compose run --rm front sh -c "yarn encore production"
```
для сборки фронт-части в dev режиме
```shell
docker-compose run --rm front sh -c "yarn encore dev"
```
в режиме наблюдения (пересобирает на лету)
```shell
docker-compose run --rm front sh -c "yarn encore dev --watch"
```

ok.
