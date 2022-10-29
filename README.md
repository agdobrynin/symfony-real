# symfony-real
Для проекта нужен docker (docker desktop) а так же docker-compose

Структура директорий проекта:
````
|
|_ app/ <-- symfony приложение и фронт часть проекта
|_ docker-files/ <-- настройка для docker контейнеров
|_ docker-compose.yml <--- файл конфига основной для запуска через docker-compose
|_ docker-compose.override.yml <--- файл дополнение для docker контейнеров
````

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
Собрать фронт часть проекта:
```shell
docker-compose run --rm front sh -c "yarn install && yarn encore production"
```
🏃🏻 Для инициализации выполнить через docker-compose команду
```shell
docker-compose run --rm php sh -c \
"composer install\
&& php bin/console doctrine:migrations:migrate -n\
&& php bin/console doctrine:fixtures:load -n"
```
📺 или же можно воспользоватся интерактивным режимом зайдя в контейнер _php_
```shell
docker-compose exec php bash
```
и выполнить в командной строке команду (мы находимся в контейнере _php_):
```shell
composer install && bin/console doctrine:migrations:migrate -n && bin/console doctrine:fixtures:load -n   
```

#### В разработке проекта
Для выполнения задач связанных с разработкой и настройкой symfony проекта можно заходить в контейнер _php_
и выполнять привычные команды для symfony: 
```shell
docker-compose exec php bash
```
появится командная строка интерпретатора **bash** запущенного внутри контейнера. Например, посмотреть существующие роуты в symfony проекте:
```shell
php bin/console debug:route
```

🚀 Открыть проект можно по адресу `http://localhost` в браузере

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
### Сборка, запуск и остановка docker контейнеров
Все команды для сборки, запуска или остановки контейнеров выопнять в корневой директории проекта (там где расположен файл **docker-compose.yml**)

Чтобы пересобрать все контейнеры заново (с нуля) выпонить команду
```shell
docker-compose build
```
Запуск контейнеров (если уже было сделана сборка контейнеров - build) чтобы начать раобтать с проектом:
```shell
docker-compose up -d
```
Для остановки docker контейнеров используйте команду:
```shell
docker-compose stop
```
Если необходимо удалить собранные контейнеры:
```shell
docker-compose down
```
после удаления собранных контейнеров необходимо будет их заново собрать командой `docker-compose build`
