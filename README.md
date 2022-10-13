# symfony-real

## Бэк часть проекта
Для настройки переменных окружения прокта необходимо скопировать файл из `.env-exmple` в `.env` и внести собственные настройки для проекта
```shell
cp .env-example .env
```
Для сборки зависимотей проекта понадоится composer версии 2.

Запустить composer для подтягивания зависмотей проекта
```shell
composer install
```
#### Выполнить миграции
```shell
php bin/console doctrine:migrations:migrate --no-interaction
```
#### Заполнить тестовыми данными таблицы
(!) Затрёт все изменения в базе данных:
```shell
php bin/console doctrine:fixtures:load -q
```
## Фронт часть проекта
Для фронт-части проекта используется пакет **@symfony/webpack-encore** и для сборки фронт-части понадобится
установелнный пакетный менеджер `yarn`. 

Инициализация фронт-части
```shell
yarn install && yarn encore production
```
для сборки фронт-части в dev режиме
```shell
yarn encore dev
```
в режиме наблюдения
```shell
yarn encore dev --watch
```

Запустить проект можно через встроенный web сервер в PHP
```bash
php -S 0.0.0.0:8080 -t public/
```
открыть в браузере адрес `http://localhost:8080/`
 
Если установлена утилита `symfony` то можно запустить через
```bash
symfony serve
```

ok.
