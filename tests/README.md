# Юнит-тесты темы

PHPUnit-тесты логики темы без загрузки ядра WordPress: тестируемые файлы из `inc/`
подключаются напрямую в `tests/bootstrap.php`, а функции WP заменены минимальными
заглушками в `tests/wp-stubs.php` (post meta, options, transients, хуки, `WP_Query`,
`$wpdb`, реестр блоков, REST). Состояние заглушек живёт в `WP_Test_State` и сбрасывается
перед каждым тестом (`Tolstenko\Tests\TestCase`).

## Запуск

```bash
composer install
composer test                # весь набор
composer test:coverage       # + текстовый отчёт покрытия (нужен pcov или xdebug)
```

`php-pcov` (или `php-xdebug`) нужен только для отчётов покрытия; тесты работают и без него.

## Что покрыто

`<source>` в `phpunit.xml` ограничивает отчёт покрытия файлами, для которых есть тесты:

| Файл | Что проверяется |
| --- | --- |
| `inc/blog/helpers.php` | TOC (уникальные id, h2/h3), счётчик кураторских комментариев, атрибуты изображений, автор статьи |
| `inc/reviews-helpers.php` | поля отзыва из meta, парсинг embed, постер rutube/youtube (+кэш), группировка по типам |
| `inc/compat-koritan-rename.php` | ремап namespace `koritan/*`, миграция options/meta/контента, алиасы блоков |
| `inc/rest-posts-filter.php` | payload фильтра (порядок ID, exclude, пагинация), плитка архива, REST-роут и allowlist типов |
| `inc/blog/allowed-blocks.php` | allowlist редактора, каталог блоков и фильтры, сохранение дефолтов из `$_POST` |
| `inc/blog/content-blocks.php` | слаги/имена блоков тела статьи, allowed HTML для iframe |
| `inc/blog/plugins-display.php` | опции Post Views Counter, скрытие авто-счётчика |
| `inc/blog/reading-time.php` | текст времени чтения (нормализация, отсутствие плагина) |

## Добавление тестов

1. Подключите нужный файл темы в `tests/bootstrap.php` и добавьте его в `<source>` в `phpunit.xml`.
2. Отсутствующие функции WP добавляйте в `tests/wp-stubs.php` — реализация должна повторять
   поведение WP настолько, насколько это нужно тесту, и не подменять код темы.
3. Тест наследуйте от `Tolstenko\Tests\TestCase` и задавайте данные через `WP_Test_State`
   или хелперы `makePost()` / `makeAttachment()` / `setMeta()`.
