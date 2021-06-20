# purchasePlan_API

## Настройка проекта
Для запуска приложения необходимо:
1. скачать программу: git clone https://github.com/twi9gy/purchasePlan_API
2. Установить необходимые пакеты: composer install
3. Запустить контейнеры приложения: make up
4. Настроить файл .env для подключения к бд.
5. Создать базу данных: docker-compose exec php bin/console doctrine:database:create
6. Применить миграцию: make migrate
7. Загрузить fixtures: make fixtload