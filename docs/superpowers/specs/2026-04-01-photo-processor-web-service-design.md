# Photo Processor Web Service — Design Spec

## Overview

B2C веб-сервіс для обробки фото на документи. Користувач завантажує фото, обирає формат документа (паспорт, віза тощо), отримує прев'ю з watermark безкоштовно, завантажує чисте фото після оплати.

## Stack

- **Оркестратор:** Laravel 13 + Filament 5
- **Фронтенд:** Blade + Livewire
- **Процесор:** існуючий FastAPI Docker контейнер (birefnet-portrait + alpha matting)
- **Черга:** Redis + Laravel Queue
- **Зберігання:** local storage

## Архітектура

```
User (браузер)
  ↓ upload фото + вибір формату
Laravel (Blade + Livewire)
  ↓ зберігає оригінал в storage
  ↓ dispatch ProcessPhotoJob
Redis Queue
  ↓ worker підхоплює job
  ↓ HTTP POST → FastAPI processor
  ↓ зберігає результат (з watermark + без)
  ↓ event PhotoProcessed
Livewire polling
  ↓ показує прев'ю з watermark
  ↓ оплата → видає чисте фото
```

### Docker Compose сервіси

- `app` — Laravel (PHP-FPM + nginx)
- `worker` — Laravel queue worker
- `redis` — черга
- `processor` — існуючий FastAPI контейнер

## Моделі даних

### `document_formats`

| Поле | Тип | Опис |
|------|-----|------|
| id | bigint | PK |
| name | string | Назва (Паспорт UA) |
| country | string(2) | Код країни (UA) |
| width_mm | integer | Ширина в мм (35) |
| height_mm | integer | Висота в мм (45) |
| dpi | integer | Роздільна здатність (300) |
| is_active | boolean | Чи активний формат |
| sort_order | integer | Порядок сортування |
| timestamps | | created_at, updated_at |

### `photo_orders`

| Поле | Тип | Опис |
|------|-----|------|
| id | bigint | PK |
| uuid | uuid | Публічний ідентифікатор в URL |
| document_format_id | foreignId | Формат документа |
| original_path | string | Шлях до оригіналу |
| result_watermark_path | string, nullable | Результат з watermark |
| result_clean_path | string, nullable | Результат без watermark |
| status | enum | pending, processing, completed, failed |
| paid_at | timestamp, nullable | Час оплати |
| expires_at | timestamp | Auto-cleanup через 24h |
| timestamps | | created_at, updated_at |

Без таблиці users — немає реєстрації. Order ідентифікується через `uuid`.

## Флоу користувача (Livewire)

Одна сторінка, 3 кроки:

### Крок 1 — Upload + вибір формату
- Drag & drop або кнопка завантаження
- Список форматів (картки або select, згруповані по країні)
- Чекбокс Privacy Policy
- Кнопка "Обробити"
- → створює `photo_order` (status: pending), зберігає оригінал, dispatch `ProcessPhotoJob`

### Крок 2 — Очікування + прев'ю
- Livewire polling кожні 2 сек перевіряє `status`
- Спінер → коли `completed` — показує прев'ю з watermark
- Якщо `failed` — повідомлення "Не вдалось обробити, спробуйте інше фото"

### Крок 3 — Оплата + завантаження
- Кнопка "Завантажити без watermark — ₴XX"
- Після оплати → `paid_at` заповнюється → redirect на download URL (`/download/{uuid}`)
- Download URL віддає чисте фото, працює 24 години

URL результату: `/result/{uuid}` — користувач може повернутись поки order не expired.

## Процесор — зміни в FastAPI

Новий контракт endpoint:

```
POST /process
  - photo: file
  - width_mm: int (35)
  - height_mm: int (45)
  - dpi: int (300)
```

- Пропорції розраховуються з `width_mm / height_mm` замість хардкоженого 3:4
- Фінальний розмір в пікселях: `width_px = width_mm / 25.4 * dpi`
- Повертає PNG потрібного розміру

## Watermark

Два файли результату зберігаються окремо:

- **result_clean** — чисте фото (генерується процесором)
- **result_watermark** — Laravel накладає watermark після отримання чистого фото

Watermark: напівпрозорий текст "PREVIEW" діагонально по всьому зображенню. Генерується через GD в Laravel.

Логіка в `ProcessPhotoJob`:
1. Отримав чисте фото від процесора → зберіг як `result_clean`
2. Наклав watermark → зберіг як `result_watermark`

Watermark на стороні Laravel (не процесора) — процесор залишається без бізнес-логіки.

## Оплата

Заглушка на першому етапі. Кнопка "Завантажити" одразу позначає order як оплачений і віддає чисте фото. Інтеграція з платіжним провайдером (LiqPay / Mono / Stripe) буде додана пізніше.

## Privacy Policy

- Статична сторінка: що збираємо (фото, IP, cookies), як зберігаємо, скільки тримаємо, як видаляємо
- Auto-cleanup cron job — видаляє всі фото (оригінали + результати) через 24 години
- Чекбокс згоди з Privacy Policy перед завантаженням фото

## Filament адмінка (мінімальна)

- CRUD для `document_formats` (назва, країна, розмір, DPI, active/inactive, sort order)
- Базова статистика: кількість оброблених фото, оплачених, за сьогодні/тиждень/місяць

## Деплой

- Один VPS (Hetzner / DigitalOcean)
- Docker Compose з усіма 4 сервісами на одній машині
- Nginx як reverse proxy перед Laravel
