# Products24 - Bitrix24 Product Integration

Products24 - это Laravel-приложение для интеграции с Bitrix24, предназначенное для управления товарами, товарными позициями и сделками.

## Описание

Приложение предоставляет веб-интерфейс для работы с продуктами из каталога Bitrix24, позволяя:

- Просматривать и фильтровать товары
- Управлять товарными позициями в сделках
- Добавлять товары в сделки непосредственно из интерфейса
- Синхронизировать данные с порталом Bitrix24

## Основные возможности

### 🔄 Синхронизация данных
- Автоматическое обновление списка компаний
- Синхронизация успешных сделок
- Обновление каталога товаров
- Синхронизация товарных позиций в сделках

### 📦 Управление товарами
- Просмотр товаров с фильтрацией
- Отображение артикулов и аналогов
- Управление ценами и количеством
- Добавление товаров в сделки

### 🎯 Интеграция с Bitrix24
- OAuth авторизация с порталом
- API для работы с CRM
- Автоматическое обновление токенов
- Поддержка множественных интеграций

## Требования

- PHP 8.2+
- Laravel 11.x
- SQLite (по умолчанию) или PostgreSQL/MySQL
- Composer
- Node.js и NPM (для фронтенда)

## Установка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd Products24
```

### 2. Установка зависимостей

```bash
# PHP зависимости
composer install

# JavaScript зависимости
npm install
```

### 3. Настройка окружения

```bash
# Копирование файла конфигурации
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate
```

### 4. Настройка базы данных

```bash
# Создание файла базы данных SQLite (если используется SQLite)
touch database/database.sqlite

# Выполнение миграций
php artisan migrate
```

### 5. Сборка фронтенда

```bash
npm run build
```

## Конфигурация

### Основные настройки в .env

```env
APP_NAME=Products24
APP_ENV=local
APP_KEY=your-app-key
APP_DEBUG=true
APP_URL=http://localhost

# База данных
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=products24
# DB_USERNAME=root
# DB_PASSWORD=

# Кэш
CACHE_STORE=database
RESPONSE_CACHE_ENABLED=true

# Логирование
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

## Использование

### Запуск приложения

```bash
# Локальная разработка
php artisan serve

# Или с помощью Laravel Sail
./vendor/bin/sail up
```

### Консольные команды

Приложение включает несколько команд для синхронизации данных:

```bash
# Обновление списка компаний
php artisan product:update-company-list

# Обновление списка сделок
php artisan product:update-deal-list

# Обновление каталога товаров
php artisan product:update-product-list

# Обновление товарных позиций в сделках
php artisan product:update-deal-item-list [update]
```

### Автоматическое выполнение команд

Команды настроены на автоматическое выполнение через планировщик Laravel:

- Обновление компаний и сделок: каждые 15 минут
- Обновление товаров и товарных позиций: каждый час

Для активации планировщика добавьте в cron:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Структура проекта

```
app/
├── Console/Commands/        # Консольные команды для синхронизации
├── Http/Controllers/        # Контроллеры приложения
├── Models/                  # Модели данных
├── Helpers/                 # Вспомогательные функции
└── Providers/              # Сервис-провайдеры

resources/
├── views/                  # Blade-шаблоны
│   └── products/          # Страницы для работы с товарами
└── js/                    # JavaScript файлы

database/
├── migrations/            # Миграции базы данных
└── factories/            # Фабрики для тестовых данных

routes/
├── web.php               # Веб-маршруты
└── console.php          # Консольные команды
```

## API Endpoints

### Основные маршруты

- `GET /` - Главная страница (товарные позиции)
- `GET /product/list/{integration}/{deal}` - Список товаров
- `GET /product/{integration}` - API для получения товаров (с кэшированием)
- `GET /product-item/{integration}` - API для получения товарных позиций

### Параметры API

- `integration` - ID интеграции с Bitrix24
- `deal` - ID сделки
- `dealId` - ID сделки (query parameter)

## Модели данных

### Integration
Хранит данные об интеграции с порталом Bitrix24:
- `domain` - домен портала
- `auth_id` - токен авторизации
- `refresh_id` - токен обновления
- `expire` - время истечения токена
- `catalogs` - список каталогов

### Product
Товары из каталога Bitrix24:
- `name` - название товара
- `price` - цена
- `fields` - дополнительные поля (JSON)

### ProductItem
Товарные позиции в сделках:
- `productName` - название товара
- `article` - артикул
- `analogs` - аналоги
- `price`, `quantity` - цена и количество
- `discountRate` - размер скидки

### Company, Deals
Компании и сделки из Bitrix24 CRM.

## Логирование

Приложение ведет детальные логи:

- `UpdateProductList.log` - логи обновления товаров
- `UpdateDealList.log` - логи обновления сделок
- `requestFromBitrix.log` - входящие запросы от Bitrix24

Логи сохраняются в `storage/logs/{date}/` с группировкой по датам.

## Кэширование

Используется Response Cache для оптимизации:
- Кэширование API ответов
- Настраиваемое время жизни кэша
- Автоматическая очистка при необходимости

## Вспомогательные функции

### `subtractPercentage($number, $percent)`
Функция для расчета цены со скидкой:

```php
$finalPrice = subtractPercentage(1000, 10); // 900
```

## Безопасность

- CSRF защита отключена для API endpoints
- Валидация входящих данных от Bitrix24
- Проверка токенов авторизации
- Логирование всех операций

## Разработка

### Требования для разработки

- PHPStorm/VS Code с поддержкой Laravel
- Xdebug для отладки
- Laravel Telescope (опционально)

### Тестирование

```bash
# Запуск тестов
php artisan test

# Запуск с покрытием
php artisan test --coverage
```

### Code Style

Проект использует Laravel Pint для форматирования кода:

```bash
./vendor/bin/pint
```

## Deployment

### Production настройки

1. Установите `APP_ENV=production`
2. Отключите `APP_DEBUG=false`
3. Настройте очередь: `QUEUE_CONNECTION=redis`
4. Используйте файловый кэш: `CACHE_STORE=file`
5. Настройте веб-сервер (Nginx/Apache)

### Оптимизация

```bash
# Кэширование конфигурации
php artisan config:cache

# Кэширование маршрутов
php artisan route:cache

# Кэширование представлений
php artisan view:cache

# Оптимизация автозагрузчика
composer install --optimize-autoloader --no-dev
```
