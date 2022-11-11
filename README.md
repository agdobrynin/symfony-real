# Курс "Learn PHP Symfony Hands-On Creating Real World Application" на платформе udemy.com

[Сертификат об успешном прохождении курса.](https://www.udemy.com/certificate/UC-bdc0458e-d782-472a-844f-ff9b6fe794fb/)

#### Структура директорий проекта:

Для проекта нужен docker (docker desktop) а так же docker-compose

````
|
|_ app/ <-- symfony приложение и фронт часть проекта
|_ docker-files/ <-- настройка для docker контейнеров
|_ docker-compose.yml <--- файл конфига основной для запуска через docker-compose
|_ docker-compose.override.yml <--- файл дополнение для docker контейнеров
````

#### Контейнеры и их назначение:

````
consumer      Консьюмер symfony
database      Postgres база (подключение через localhost порт 5432)
front         Контейнерс с NodeJs и фронтовой частью (yarn и symfony encore)
mailcatcher   Для разработки и отладки отправки писем с symfony
              с интерфейсом просмотра писем http://localhost:1080
nginx         Вэб сервер проекта по адресу http://localhost
php           Контейнер с symfony
````

## Сборка контейнеров и настройка проекта

Для настройки переменных окружения **docker** контейнеров необходимо скопировать файл и внести собственные настройки

```shell
cp .env-example .env
```

Для настройки переменных окружения **symfony** прокта необходимо скопировать файл и внести собственные настройки для
проекта

```shell
cp app/.env-example app/.env
```

собираем контенеры и стратуем docker
```shell
docker-compose up -d --build
```
Собрать фронт часть проекта:
```shell
docker-compose run --rm front sh -c "yarn install && yarn encore production"
```

🏃🏻 Для загрузки тестовых данных "fixtures" выполнить через docker-compose команду
```shell
docker-compose run --rm php sh -c "php bin/console doctrine:fixtures:load -n"
```
📺 или же можно воспользоватся интерактивным режимом зайдя в контейнер _php_
```shell
docker-compose exec php bash
```

#### В разработке проекта

Для выполнения задач связанных с разработкой и настройкой symfony проекта можно заходить в контейнер _php_
и выполнять привычные команды для symfony:

```shell
docker-compose exec php bash
```

появится командная строка интерпретатора **bash** запущенного внутри контейнера. Например, посмотреть существующие роуты
в symfony проекте:

```shell
php bin/console debug:route
```

🚀 Основной проект доступен по адресу

```
http://localhost
```

📧 Для отладки отправки писем используется MailCatcher доступен по адресу

```
http://localhost:1080/
```

#### Запуск тестов

В проекте реализовано тетирование 3х уровней - Unit тесты, Integration тесты, Functional тесты

Для запуска всех тестов достаточно выполнить команду:

```shell
docker-compose run --rm php sh -c 'make tests-all'
```

для запуска только Unit тестов

```shell
docker-compose run --rm php sh -c 'make test-unit'
```

для запуска Integration тестов

```shell
docker-compose run --rm php sh -c 'make tests-integration'
```

для запуска Functional тестов

```shell
docker-compose run --rm php sh -c 'make tests-functional'
```

Так же в ходе разработки тестов может быть удобно выполнять тесты в контейнере **php**
для этого необходимо зайти в запущенный контейнер

```shell
docker-compose exec php bash
```

и выполнять привычные команды PHPUnit уже в контейнере. Например запусить все тесты находясь в контейнере **php**

```shell
make tests-all
--- или же ---
php bin/phpunit
```

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
