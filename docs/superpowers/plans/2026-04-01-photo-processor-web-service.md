# Photo Processor Web Service Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** B2C веб-сервіс для обробки фото на документи — Laravel + Livewire фронтенд, FastAPI процесор, Filament адмінка, Docker Compose.

**Architecture:** Laravel 13 оркеструє флоу: зберігає фото в local storage, ставить job у Redis чергу; worker відправляє в FastAPI processor, зберігає clean + watermark результати; Livewire polling показує прев'ю; payment stub дозволяє завантажити чисте фото. Без авторизації — order ідентифікується UUID.

**Tech Stack:** Laravel 13, Filament 5, Livewire 3, PHP 8.3, SQLite, Redis, FastAPI Python 3.11, Docker Compose, GD extension (watermark), Pest (PHP tests), pytest (Python tests)

---

## File Structure

```
photo-processor/
├── app/                                          ← Laravel (новий)
│   ├── app/
│   │   ├── Models/
│   │   │   ├── DocumentFormat.php
│   │   │   └── PhotoOrder.php
│   │   ├── Jobs/
│   │   │   └── ProcessPhotoJob.php
│   │   ├── Services/
│   │   │   ├── PhotoProcessorClient.php
│   │   │   └── WatermarkService.php
│   │   ├── Livewire/
│   │   │   └── PhotoProcessor.php
│   │   ├── Http/Controllers/
│   │   │   └── PhotoController.php
│   │   ├── Console/Commands/
│   │   │   └── CleanExpiredOrders.php
│   │   └── Filament/
│   │       ├── Resources/DocumentFormatResource.php
│   │       └── Pages/StatsPage.php
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── xxxx_create_document_formats_table.php
│   │   │   └── xxxx_create_photo_orders_table.php
│   │   └── seeders/DocumentFormatSeeder.php
│   ├── resources/views/
│   │   ├── layouts/app.blade.php
│   │   ├── livewire/photo-processor.blade.php
│   │   └── pages/privacy-policy.blade.php
│   ├── routes/web.php
│   └── Dockerfile
├── processor/
│   ├── main.py                                   ← оновити: Form params
│   ├── processor.py                              ← оновити: width_mm/height_mm/dpi
│   ├── requirements.txt                          ← додати pytest pytest-mock
│   └── tests/test_processor.py                  ← новий
└── docker-compose.yml                            ← оновити: +app +worker +redis
```

---

## Task 1: FastAPI — параметризований endpoint

**Files:**
- Modify: `processor/processor.py`
- Modify: `processor/main.py`
- Modify: `processor/requirements.txt`
- Create: `processor/tests/__init__.py`
- Create: `processor/tests/test_processor.py`

- [ ] **Step 1: Додати pytest до requirements**

```
# processor/requirements.txt
rembg[cpu]
mediapipe
pillow
fastapi
uvicorn
python-multipart
pytest
pytest-mock
```

- [ ] **Step 2: Написати падаючий тест**

```python
# processor/tests/__init__.py
# (порожній файл)
```

```python
# processor/tests/test_processor.py
import io
from unittest.mock import patch
from PIL import Image
from processor import process_photo


def make_test_image(width: int = 200, height: int = 250) -> bytes:
    img = Image.new("RGBA", (width, height), color=(200, 180, 160, 255))
    buf = io.BytesIO()
    img.save(buf, format="PNG")
    return buf.getvalue()


@patch("processor.remove_background")
@patch("processor.detect_face")
def test_process_photo_returns_correct_pixel_size(mock_face, mock_bg):
    mock_face.return_value = None

    def passthrough(img):
        return img.convert("RGBA")

    mock_bg.side_effect = passthrough

    result = process_photo(make_test_image(), width_mm=35, height_mm=45, dpi=300)
    img = Image.open(io.BytesIO(result))

    assert img.size == (int(35 / 25.4 * 300), int(45 / 25.4 * 300))  # (413, 531)


@patch("processor.remove_background")
@patch("processor.detect_face")
def test_process_photo_square_format(mock_face, mock_bg):
    mock_face.return_value = None

    def passthrough(img):
        return img.convert("RGBA")

    mock_bg.side_effect = passthrough

    result = process_photo(make_test_image(), width_mm=51, height_mm=51, dpi=300)
    img = Image.open(io.BytesIO(result))
    w, h = img.size

    assert w == h  # квадратний (USA Visa)


@patch("processor.remove_background")
@patch("processor.detect_face")
def test_process_photo_default_params(mock_face, mock_bg):
    """Без параметрів — 35×45мм 300dpi (паспорт UA)."""
    mock_face.return_value = None

    def passthrough(img):
        return img.convert("RGBA")

    mock_bg.side_effect = passthrough

    result = process_photo(make_test_image())
    img = Image.open(io.BytesIO(result))

    assert img.size == (int(35 / 25.4 * 300), int(45 / 25.4 * 300))
```

- [ ] **Step 3: Запустити — переконатись що тест падає**

```bash
cd /Users/ernestbehinov/Work/photo-processor/processor
pip install pytest pytest-mock
pytest tests/ -v
```

Очікуємо: `FAILED` — `process_photo() takes 1 positional argument but 4 were given`

- [ ] **Step 4: Оновити `processor.py`**

Замінити весь файл:

```python
# processor/processor.py
import io

import mediapipe as mp
import numpy as np
from PIL import Image
from rembg import new_session, remove

_rembg_session = new_session("birefnet-portrait")

_face_detection = mp.solutions.face_detection.FaceDetection(
    model_selection=1, min_detection_confidence=0.5
)


def detect_face(image: Image.Image) -> tuple[int, int, int, int] | None:
    """Повертає bbox обличчя (x, y, w, h) або None."""
    rgb = np.array(image)
    results = _face_detection.process(rgb)

    if not results.detections:
        return None

    detection = results.detections[0]
    bbox = detection.location_data.relative_bounding_box
    w, h = image.size

    face_x = int(bbox.xmin * w)
    face_y = int(bbox.ymin * h)
    face_w = int(bbox.width * w)
    face_h = int(bbox.height * h)

    return (face_x, face_y, face_w, face_h)


def remove_background(image: Image.Image) -> Image.Image:
    """Видаляє фон через rembg з alpha matting."""
    return remove(
        image,
        session=_rembg_session,
        alpha_matting=True,
        alpha_matting_foreground_threshold=240,
        alpha_matting_background_threshold=10,
        alpha_matting_erode_size=10,
    )


def compose_document_photo(
    image: Image.Image,
    face: tuple[int, int, int, int],
    doc_ratio: float = 3 / 4,
) -> Image.Image:
    """Компонує документне фото з правильним кадруванням під задані пропорції."""
    face_x, face_y, face_w, face_h = face
    img_w, img_h = image.size

    face_center_x = face_x + face_w // 2

    target_h = int(face_h / 0.35)
    target_w = int(target_h * doc_ratio)

    top = face_y - int(target_h * 0.25)
    left = face_center_x - target_w // 2

    top = max(0, top)
    left = max(0, left)
    if left + target_w > img_w:
        left = max(0, img_w - target_w)
    if top + target_h > img_h:
        top = max(0, img_h - target_h)

    right = min(img_w, left + target_w)
    bottom = min(img_h, top + target_h)

    cropped = image.crop((left, top, right, bottom))

    if cropped.width < target_w or cropped.height < target_h:
        canvas = Image.new("RGBA", (target_w, target_h), (255, 255, 255, 255))
        paste_x = (target_w - cropped.width) // 2
        canvas.paste(cropped, (paste_x, 0), cropped if cropped.mode == "RGBA" else None)
        return canvas

    return cropped


def add_white_background(image: Image.Image) -> Image.Image:
    """Додає білий фон до RGBA зображення."""
    if image.mode != "RGBA":
        return image
    background = Image.new("RGBA", image.size, (255, 255, 255, 255))
    background.paste(image, mask=image.split()[3])
    return background.convert("RGB")


def process_photo(
    image_bytes: bytes,
    width_mm: int = 35,
    height_mm: int = 45,
    dpi: int = 300,
) -> bytes:
    """Повний пайплайн: видаляє фон, кадрує, масштабує до потрібного розміру."""
    width_px = int(width_mm / 25.4 * dpi)
    height_px = int(height_mm / 25.4 * dpi)
    doc_ratio = width_mm / height_mm

    image = Image.open(io.BytesIO(image_bytes)).convert("RGB")

    face = detect_face(image)
    no_bg = remove_background(image)

    if face:
        composed = compose_document_photo(no_bg, face, doc_ratio)
    else:
        composed = no_bg

    result = add_white_background(composed)
    result = result.resize((width_px, height_px), Image.LANCZOS)

    output = io.BytesIO()
    result.save(output, format="PNG")
    return output.getvalue()
```

- [ ] **Step 5: Оновити `main.py`**

```python
# processor/main.py
from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import Response

from processor import process_photo

app = FastAPI(title="Photo Processor")


@app.post("/process")
async def process(
    photo: UploadFile = File(...),
    width_mm: int = Form(35),
    height_mm: int = Form(45),
    dpi: int = Form(300),
):
    """Обробляє фото: видаляє фон, кадрує, повертає PNG потрібного розміру."""
    image_bytes = await photo.read()
    result = process_photo(image_bytes, width_mm, height_mm, dpi)
    return Response(content=result, media_type="image/png")
```

- [ ] **Step 6: Запустити тести — переконатись що проходять**

```bash
cd /Users/ernestbehinov/Work/photo-processor/processor
pytest tests/ -v
```

Очікуємо: `3 passed`

- [ ] **Step 7: Commit**

```bash
cd /Users/ernestbehinov/Work/photo-processor
git add processor/
git commit -m "feat(processor): parametrize endpoint with width_mm/height_mm/dpi"
```

---

## Task 2: Laravel project + Docker Compose

**Files:**
- Create: `app/` (Laravel project)
- Create: `app/Dockerfile`
- Create: `app/docker/nginx.conf`
- Modify: `docker-compose.yml`

- [ ] **Step 1: Створити Laravel проект**

```bash
cd /Users/ernestbehinov/Work/photo-processor
composer create-project laravel/laravel app --prefer-dist
```

- [ ] **Step 2: Встановити залежності**

```bash
cd app
composer require filament/filament:"^5.0" livewire/livewire:"^3.0" predis/predis
composer require --dev pestphp/pest:"^3.0" pestphp/pest-plugin-laravel:"^3.0"
./vendor/bin/pest --init
```

- [ ] **Step 3: Встановити Filament panel**

```bash
php artisan filament:install --panels
```

Коли запитає ім'я панелі — ввести `admin`.

- [ ] **Step 4: Налаштувати `.env` для SQLite + Redis**

```bash
cp .env.example .env
php artisan key:generate
```

Відкрити `app/.env` і замінити секцію DB + Redis:

```dotenv
APP_NAME="Photo Processor"
APP_URL=http://localhost

DB_CONNECTION=sqlite
# DB_HOST та інші рядки для mysql — прибрати або залишити закоментованими

QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

PROCESSOR_URL=http://processor:8000
```

- [ ] **Step 5: Перевірити що SQLite база створюється**

```bash
touch database/database.sqlite
php artisan migrate
```

Очікуємо: `INFO  Running migrations.` без помилок.

- [ ] **Step 6: Створити `app/Dockerfile`**

```dockerfile
# app/Dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    ttf-dejavu \
    sqlite sqlite-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p storage/app/originals storage/app/results storage/framework/cache \
    storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
```

- [ ] **Step 7: Створити nginx конфіг**

```bash
mkdir -p app/docker
```

```nginx
# app/docker/nginx.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    server {
        listen 80;
        server_name _;
        root /var/www/html/public;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
```

- [ ] **Step 8: Оновити `docker-compose.yml`**

```yaml
# docker-compose.yml
services:
  app:
    build:
      context: ./app
      dockerfile: Dockerfile
    ports:
      - "80:80"
    volumes:
      - ./app:/var/www/html
      - app-storage:/var/www/html/storage/app
    environment:
      APP_KEY: "${APP_KEY}"
      APP_ENV: local
      DB_CONNECTION: sqlite
      QUEUE_CONNECTION: redis
      REDIS_HOST: redis
      PROCESSOR_URL: http://processor:8000
    depends_on:
      - redis
      - processor

  worker:
    build:
      context: ./app
      dockerfile: Dockerfile
    command: php artisan queue:work redis --sleep=3 --tries=3
    volumes:
      - ./app:/var/www/html
      - app-storage:/var/www/html/storage/app
    environment:
      APP_KEY: "${APP_KEY}"
      APP_ENV: local
      DB_CONNECTION: sqlite
      QUEUE_CONNECTION: redis
      REDIS_HOST: redis
      PROCESSOR_URL: http://processor:8000
    depends_on:
      - redis
      - processor

  redis:
    image: redis:7-alpine
    volumes:
      - redis-data:/data

  processor:
    build: ./processor
    volumes:
      - ./processor:/app
      - rembg-models:/root/.u2net
    environment:
      PYTHONUNBUFFERED: "1"

volumes:
  rembg-models:
  redis-data:
  app-storage:
```

- [ ] **Step 9: Commit**

```bash
cd /Users/ernestbehinov/Work/photo-processor
git add app/ docker-compose.yml
git commit -m "feat: add Laravel app scaffolding + Docker Compose setup"
```

---

## Task 3: Migrations + Models

**Files:**
- Create: `app/database/migrations/xxxx_create_document_formats_table.php`
- Create: `app/database/migrations/xxxx_create_photo_orders_table.php`
- Create: `app/app/Models/DocumentFormat.php`
- Create: `app/app/Models/PhotoOrder.php`
- Create: `app/tests/Unit/Models/DocumentFormatTest.php`
- Create: `app/tests/Unit/Models/PhotoOrderTest.php`

- [ ] **Step 1: Написати тести для моделей (падаючі)**

```php
<?php
// app/tests/Unit/Models/DocumentFormatTest.php

use App\Models\DocumentFormat;

it('scope active returns only active formats ordered by sort_order', function () {
    DocumentFormat::factory()->create(['is_active' => true, 'sort_order' => 2]);
    DocumentFormat::factory()->create(['is_active' => true, 'sort_order' => 1]);
    DocumentFormat::factory()->create(['is_active' => false, 'sort_order' => 0]);

    $active = DocumentFormat::active()->get();

    expect($active)->toHaveCount(2)
        ->and($active->first()->sort_order)->toBe(1);
});
```

```php
<?php
// app/tests/Unit/Models/PhotoOrderTest.php

use App\Models\PhotoOrder;
use App\Models\DocumentFormat;
use Illuminate\Support\Str;

it('auto-generates uuid and expires_at on creating', function () {
    $format = DocumentFormat::factory()->create();

    $order = PhotoOrder::create([
        'document_format_id' => $format->id,
        'original_path' => 'originals/test.png',
    ]);

    expect($order->uuid)->not->toBeNull()
        ->and(Str::isUuid($order->uuid))->toBeTrue()
        ->and($order->expires_at)->not->toBeNull()
        ->and($order->expires_at->diffInHours(now()))->toBeGreaterThanOrEqual(23);
});

it('scope expired returns only expired orders', function () {
    $format = DocumentFormat::factory()->create();

    PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'expires_at' => now()->subHour(),
    ]);
    PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'expires_at' => now()->addHour(),
    ]);

    expect(PhotoOrder::expired()->count())->toBe(1);
});

it('isPaid returns true only when paid_at is set', function () {
    $format = DocumentFormat::factory()->create();
    $order = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'paid_at' => null,
    ]);

    expect($order->isPaid())->toBeFalse();

    $order->update(['paid_at' => now()]);

    expect($order->fresh()->isPaid())->toBeTrue();
});
```

- [ ] **Step 2: Запустити тести — переконатись що падають**

```bash
cd /Users/ernestbehinov/Work/photo-processor/app
php artisan test tests/Unit/Models/ --pest
```

Очікуємо: `FAILED` — `Class "App\Models\DocumentFormat" not found`

- [ ] **Step 3: Створити міграцію document_formats**

```bash
php artisan make:migration create_document_formats_table
```

Відкрити створений файл (`database/migrations/xxxx_create_document_formats_table.php`) і замінити метод `up`:

```php
public function up(): void
{
    Schema::create('document_formats', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->char('country', 2);
        $table->unsignedSmallInteger('width_mm');
        $table->unsignedSmallInteger('height_mm');
        $table->unsignedSmallInteger('dpi')->default(300);
        $table->boolean('is_active')->default(true);
        $table->unsignedSmallInteger('sort_order')->default(0);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('document_formats');
}
```

- [ ] **Step 4: Створити міграцію photo_orders**

```bash
php artisan make:migration create_photo_orders_table
```

```php
public function up(): void
{
    Schema::create('photo_orders', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->foreignId('document_format_id')->constrained('document_formats');
        $table->string('original_path');
        $table->string('result_watermark_path')->nullable();
        $table->string('result_clean_path')->nullable();
        $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
        $table->timestamp('paid_at')->nullable();
        $table->timestamp('expires_at');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('photo_orders');
}
```

- [ ] **Step 5: Створити модель DocumentFormat**

```php
<?php
// app/app/Models/DocumentFormat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DocumentFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'country', 'width_mm', 'height_mm', 'dpi', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
```

- [ ] **Step 6: Створити модель PhotoOrder**

```php
<?php
// app/app/Models/PhotoOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PhotoOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'document_format_id', 'original_path',
        'result_watermark_path', 'result_clean_path',
        'status', 'paid_at', 'expires_at',
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PhotoOrder $order) {
            $order->uuid       ??= (string) Str::uuid();
            $order->expires_at ??= now()->addHours(24);
        });
    }

    public function documentFormat(): BelongsTo
    {
        return $this->belongsTo(DocumentFormat::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }
}
```

- [ ] **Step 7: Створити factories**

```bash
php artisan make:factory DocumentFormatFactory --model=DocumentFormat
php artisan make:factory PhotoOrderFactory --model=PhotoOrder
```

```php
<?php
// app/database/factories/DocumentFormatFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFormatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->words(2, true),
            'country'    => fake()->countryCode(),
            'width_mm'   => 35,
            'height_mm'  => 45,
            'dpi'        => 300,
            'is_active'  => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
```

```php
<?php
// app/database/factories/PhotoOrderFactory.php

namespace Database\Factories;

use App\Models\DocumentFormat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PhotoOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'               => (string) Str::uuid(),
            'document_format_id' => DocumentFormat::factory(),
            'original_path'      => 'originals/' . fake()->uuid() . '.png',
            'status'             => 'pending',
            'expires_at'         => now()->addHours(24),
        ];
    }
}
```

- [ ] **Step 8: Запустити міграцію та тести**

```bash
php artisan migrate
php artisan test tests/Unit/Models/ --pest
```

Очікуємо: `3 passed`

- [ ] **Step 9: Commit**

```bash
git add database/ app/Models/ tests/Unit/Models/
git commit -m "feat: add DocumentFormat and PhotoOrder models with migrations"
```

---

## Task 4: PhotoProcessorClient

**Files:**
- Create: `app/app/Services/PhotoProcessorClient.php`
- Create: `app/tests/Unit/Services/PhotoProcessorClientTest.php`

- [ ] **Step 1: Написати падаючий тест**

```php
<?php
// app/tests/Unit/Services/PhotoProcessorClientTest.php

use App\Services\PhotoProcessorClient;
use Illuminate\Support\Facades\Http;

it('sends multipart POST with photo and dimension params', function () {
    Http::fake([
        'http://processor:8000/process' => Http::response(
            file_get_contents(base_path('tests/fixtures/1x1.png')),
            200,
            ['Content-Type' => 'image/png']
        ),
    ]);

    $client = new PhotoProcessorClient('http://processor:8000');
    $result = $client->process('fake-image-bytes', 35, 45, 300);

    expect($result)->toBeString()->not->toBeEmpty();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/process')
            && $request->isMultipart();
    });
});

it('throws RuntimeException when processor returns error', function () {
    Http::fake([
        'http://processor:8000/process' => Http::response('error', 500),
    ]);

    $client = new PhotoProcessorClient('http://processor:8000');

    expect(fn () => $client->process('bytes', 35, 45, 300))
        ->toThrow(\RuntimeException::class);
});
```

- [ ] **Step 2: Створити тестовий PNG fixture**

```bash
mkdir -p app/tests/fixtures
# Створюємо мінімальний PNG 1x1 через PHP
php -r "
\$img = imagecreate(1, 1);
imagecolorallocate(\$img, 255, 255, 255);
imagepng(\$img, 'tests/fixtures/1x1.png');
imagedestroy(\$img);
echo 'created';
" 
```

- [ ] **Step 3: Запустити — переконатись що тест падає**

```bash
php artisan test tests/Unit/Services/PhotoProcessorClientTest.php --pest
```

Очікуємо: `FAILED` — `Class "App\Services\PhotoProcessorClient" not found`

- [ ] **Step 4: Реалізувати PhotoProcessorClient**

```php
<?php
// app/app/Services/PhotoProcessorClient.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PhotoProcessorClient
{
    public function __construct(
        private readonly string $baseUrl = 'http://processor:8000'
    ) {}

    public function process(string $imageBytes, int $widthMm, int $heightMm, int $dpi): string
    {
        $response = Http::attach('photo', $imageBytes, 'photo.png', ['Content-Type' => 'image/png'])
            ->post("{$this->baseUrl}/process", [
                'width_mm'  => (string) $widthMm,
                'height_mm' => (string) $heightMm,
                'dpi'       => (string) $dpi,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Photo processor returned HTTP {$response->status()}");
        }

        return $response->body();
    }
}
```

- [ ] **Step 5: Зареєструвати в сервіс-провайдері**

Відкрити `app/Providers/AppServiceProvider.php` і додати в метод `register`:

```php
use App\Services\PhotoProcessorClient;

public function register(): void
{
    $this->app->singleton(PhotoProcessorClient::class, function () {
        return new PhotoProcessorClient(config('services.processor.url', 'http://processor:8000'));
    });
}
```

Додати в `app/config/services.php`:

```php
'processor' => [
    'url' => env('PROCESSOR_URL', 'http://processor:8000'),
],
```

- [ ] **Step 6: Запустити тести**

```bash
php artisan test tests/Unit/Services/PhotoProcessorClientTest.php --pest
```

Очікуємо: `2 passed`

- [ ] **Step 7: Commit**

```bash
git add app/Services/PhotoProcessorClient.php app/Providers/AppServiceProvider.php config/services.php tests/
git commit -m "feat: add PhotoProcessorClient with HTTP facade integration"
```

---

## Task 5: WatermarkService

**Files:**
- Create: `app/app/Services/WatermarkService.php`
- Create: `app/tests/Unit/Services/WatermarkServiceTest.php`

- [ ] **Step 1: Написати падаючий тест**

```php
<?php
// app/tests/Unit/Services/WatermarkServiceTest.php

use App\Services\WatermarkService;

function make_test_png(int $width = 200, int $height = 250): string
{
    $img = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);
    ob_start();
    imagepng($img);
    $bytes = ob_get_clean();
    imagedestroy($img);
    return $bytes;
}

it('returns PNG bytes', function () {
    $service = new WatermarkService();
    $result = $service->apply(make_test_png());

    expect($result)->toBeString()->not->toBeEmpty();

    $info = getimagesizefromstring($result);
    expect($info['mime'])->toBe('image/png');
});

it('preserves original image dimensions', function () {
    $service = new WatermarkService();
    $original = make_test_png(413, 531);
    $result = $service->apply($original);

    [$origW, $origH] = getimagesizefromstring($original);
    [$resW, $resH] = getimagesizefromstring($result);

    expect($resW)->toBe($origW)
        ->and($resH)->toBe($origH);
});

it('throws RuntimeException for invalid bytes', function () {
    $service = new WatermarkService();

    expect(fn () => $service->apply('not-an-image'))
        ->toThrow(\RuntimeException::class);
});
```

- [ ] **Step 2: Запустити — переконатись що падає**

```bash
php artisan test tests/Unit/Services/WatermarkServiceTest.php --pest
```

Очікуємо: `FAILED` — `Class "App\Services\WatermarkService" not found`

- [ ] **Step 3: Реалізувати WatermarkService**

```php
<?php
// app/app/Services/WatermarkService.php

namespace App\Services;

class WatermarkService
{
    private string $fontPath;

    public function __construct()
    {
        // ttf-dejavu встановлений в Dockerfile; для локальної розробки — override через env
        $this->fontPath = env(
            'DEJAVU_FONT_PATH',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'
        );
    }

    public function apply(string $imageBytes): string
    {
        $image = imagecreatefromstring($imageBytes);
        if ($image === false) {
            throw new \RuntimeException('Cannot create image from provided bytes');
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        // Напівпрозорий сірий колір (alpha 0=опакий, 127=повністю прозорий)
        $textColor = imagecolorallocatealpha($image, 100, 100, 100, 55);

        $fontSize = (int) max(20, min($width, $height) / 6);
        $step     = (int) max(80, min($width, $height) / 2);
        $angle    = -45;

        // Малюємо "PREVIEW" по діагоналі кілька разів щоб покрити все зображення
        for ($y = -$width; $y <= $height + $width; $y += $step) {
            if (file_exists($this->fontPath)) {
                imagettftext($image, $fontSize, $angle, 0, $y, $textColor, $this->fontPath, 'PREVIEW');
            } else {
                // fallback: вбудований шрифт (без повороту)
                imagestring($image, 5, (int) ($width * 0.1), $y, 'PREVIEW', $textColor);
            }
        }

        ob_start();
        imagepng($image);
        $result = ob_get_clean();
        imagedestroy($image);

        return $result;
    }
}
```

- [ ] **Step 4: Запустити тести**

```bash
php artisan test tests/Unit/Services/WatermarkServiceTest.php --pest
```

Очікуємо: `3 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Services/WatermarkService.php tests/Unit/Services/WatermarkServiceTest.php
git commit -m "feat: add WatermarkService with diagonal PREVIEW overlay via GD"
```

---

## Task 6: ProcessPhotoJob

**Files:**
- Create: `app/app/Jobs/ProcessPhotoJob.php`
- Create: `app/tests/Unit/Jobs/ProcessPhotoJobTest.php`

- [ ] **Step 1: Написати падаючий тест**

```php
<?php
// app/tests/Unit/Jobs/ProcessPhotoJobTest.php

use App\Jobs\ProcessPhotoJob;
use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use App\Services\PhotoProcessorClient;
use App\Services\WatermarkService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('processes photo: sets completed status and saves clean+watermark paths', function () {
    $format = DocumentFormat::factory()->create(['width_mm' => 35, 'height_mm' => 45, 'dpi' => 300]);
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/test.png',
        'status'             => 'pending',
    ]);

    Storage::put('originals/test.png', 'fake-image-bytes');

    $fakeCleanBytes     = file_get_contents(base_path('tests/fixtures/1x1.png'));
    $fakeWatermarkBytes = file_get_contents(base_path('tests/fixtures/1x1.png'));

    $mockClient    = Mockery::mock(PhotoProcessorClient::class);
    $mockWatermark = Mockery::mock(WatermarkService::class);

    $mockClient->shouldReceive('process')
        ->once()
        ->with('fake-image-bytes', 35, 45, 300)
        ->andReturn($fakeCleanBytes);

    $mockWatermark->shouldReceive('apply')
        ->once()
        ->with($fakeCleanBytes)
        ->andReturn($fakeWatermarkBytes);

    $job = new ProcessPhotoJob($order);
    $job->handle($mockClient, $mockWatermark);

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->result_clean_path)->toBe("results/{$order->uuid}_clean.png")
        ->and($order->result_watermark_path)->toBe("results/{$order->uuid}_watermark.png");

    Storage::assertExists("results/{$order->uuid}_clean.png");
    Storage::assertExists("results/{$order->uuid}_watermark.png");
});

it('sets status failed when processor throws', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/test.png',
    ]);

    Storage::put('originals/test.png', 'bytes');

    $mockClient    = Mockery::mock(PhotoProcessorClient::class);
    $mockWatermark = Mockery::mock(WatermarkService::class);

    $mockClient->shouldReceive('process')->andThrow(new \RuntimeException('Processor down'));

    $job = new ProcessPhotoJob($order);

    expect(fn () => $job->handle($mockClient, $mockWatermark))
        ->toThrow(\RuntimeException::class);

    expect($order->fresh()->status)->toBe('failed');
});
```

- [ ] **Step 2: Запустити — переконатись що падає**

```bash
php artisan test tests/Unit/Jobs/ --pest
```

Очікуємо: `FAILED` — `Class "App\Jobs\ProcessPhotoJob" not found`

- [ ] **Step 3: Створити ProcessPhotoJob**

```bash
php artisan make:job ProcessPhotoJob
```

Замінити зміст файла:

```php
<?php
// app/app/Jobs/ProcessPhotoJob.php

namespace App\Jobs;

use App\Models\PhotoOrder;
use App\Services\PhotoProcessorClient;
use App\Services\WatermarkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly PhotoOrder $order) {}

    public function handle(PhotoProcessorClient $client, WatermarkService $watermark): void
    {
        $this->order->update(['status' => 'processing']);

        try {
            $format        = $this->order->documentFormat;
            $originalBytes = Storage::get($this->order->original_path);

            $cleanBytes = $client->process(
                $originalBytes,
                $format->width_mm,
                $format->height_mm,
                $format->dpi,
            );

            $cleanPath = "results/{$this->order->uuid}_clean.png";
            Storage::put($cleanPath, $cleanBytes);

            $watermarkBytes = $watermark->apply($cleanBytes);
            $watermarkPath  = "results/{$this->order->uuid}_watermark.png";
            Storage::put($watermarkPath, $watermarkBytes);

            $this->order->update([
                'status'                => 'completed',
                'result_clean_path'     => $cleanPath,
                'result_watermark_path' => $watermarkPath,
            ]);
        } catch (\Throwable $e) {
            $this->order->update(['status' => 'failed']);
            throw $e;
        }
    }
}
```

- [ ] **Step 4: Запустити тести**

```bash
php artisan test tests/Unit/Jobs/ --pest
```

Очікуємо: `2 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/ProcessPhotoJob.php tests/Unit/Jobs/
git commit -m "feat: add ProcessPhotoJob with clean+watermark storage"
```

---

## Task 7: Livewire PhotoProcessor component

**Files:**
- Create: `app/app/Livewire/PhotoProcessor.php`
- Create: `app/resources/views/livewire/photo-processor.blade.php`
- Create: `app/tests/Feature/Livewire/PhotoProcessorTest.php`

- [ ] **Step 1: Написати падаючі тести**

```php
<?php
// app/tests/Feature/Livewire/PhotoProcessorTest.php

use App\Jobs\ProcessPhotoJob;
use App\Livewire\PhotoProcessor;
use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

it('renders step 1 with document formats', function () {
    DocumentFormat::factory()->create(['name' => 'Паспорт UA', 'is_active' => true]);

    Livewire::test(PhotoProcessor::class)
        ->assertSee('Паспорт UA')
        ->assertSet('step', 1);
});

it('submits photo: creates order, dispatches job, moves to step 2', function () {
    $format = DocumentFormat::factory()->create();
    $photo  = UploadedFile::fake()->image('portrait.jpg', 400, 500);

    Livewire::test(PhotoProcessor::class)
        ->set('documentFormatId', $format->id)
        ->set('privacyAccepted', true)
        ->set('photo', $photo)
        ->call('submit')
        ->assertSet('step', 2)
        ->assertSet('orderUuid', fn ($uuid) => $uuid !== null);

    expect(PhotoOrder::count())->toBe(1);
    Queue::assertPushed(ProcessPhotoJob::class);
});

it('validation rejects missing photo', function () {
    $format = DocumentFormat::factory()->create();

    Livewire::test(PhotoProcessor::class)
        ->set('documentFormatId', $format->id)
        ->set('privacyAccepted', true)
        ->call('submit')
        ->assertHasErrors(['photo' => 'required']);
});

it('validation rejects unchecked privacy policy', function () {
    $format = DocumentFormat::factory()->create();
    $photo  = UploadedFile::fake()->image('photo.jpg', 400, 500);

    Livewire::test(PhotoProcessor::class)
        ->set('documentFormatId', $format->id)
        ->set('privacyAccepted', false)
        ->set('photo', $photo)
        ->call('submit')
        ->assertHasErrors(['privacyAccepted']);
});

it('checkStatus moves nothing until completed', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'processing',
    ]);

    Livewire::test(PhotoProcessor::class)
        ->set('step', 2)
        ->set('orderUuid', $order->uuid)
        ->call('checkStatus')
        ->assertSet('step', 2);
});

it('pay marks order as paid and redirects to download', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'completed',
    ]);

    Livewire::test(PhotoProcessor::class)
        ->set('step', 2)
        ->set('orderUuid', $order->uuid)
        ->call('pay')
        ->assertRedirect(route('download', $order->uuid));

    expect($order->fresh()->isPaid())->toBeTrue();
});
```

- [ ] **Step 2: Запустити — переконатись що падають**

```bash
php artisan test tests/Feature/Livewire/ --pest
```

Очікуємо: `FAILED` — `Class "App\Livewire\PhotoProcessor" not found`

- [ ] **Step 3: Реалізувати Livewire компонент**

```php
<?php
// app/app/Livewire/PhotoProcessor.php

namespace App\Livewire;

use App\Jobs\ProcessPhotoJob;
use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PhotoProcessor extends Component
{
    use WithFileUploads;

    #[Validate('required|image|max:10240')]
    public mixed $photo = null;

    #[Validate('required|exists:document_formats,id')]
    public int $documentFormatId = 0;

    #[Validate('accepted')]
    public bool $privacyAccepted = false;

    public int $step = 1;
    public ?string $orderUuid = null;

    public function submit(): void
    {
        $this->validate();

        $path = $this->photo->store('originals', 'local');

        $order = PhotoOrder::create([
            'document_format_id' => $this->documentFormatId,
            'original_path'      => $path,
        ]);

        $this->orderUuid = $order->uuid;
        $this->step      = 2;

        ProcessPhotoJob::dispatch($order);
    }

    public function checkStatus(): void
    {
        if (!$this->orderUuid || $this->step !== 2) {
            return;
        }

        $order = PhotoOrder::where('uuid', $this->orderUuid)->firstOrFail();

        if (in_array($order->status, ['completed', 'failed'])) {
            // Компонент залишається на step 2; шаблон показує різний UI залежно від status
            $this->dispatch('status-updated');
        }
    }

    public function pay(): void
    {
        $order = PhotoOrder::where('uuid', $this->orderUuid)->firstOrFail();
        $order->update(['paid_at' => now()]);
        $this->redirect(route('download', $order->uuid));
    }

    public function render(): \Illuminate\View\View
    {
        $formats = DocumentFormat::active()->get()->groupBy('country');
        $order   = $this->orderUuid
            ? PhotoOrder::where('uuid', $this->orderUuid)->first()
            : null;

        return view('livewire.photo-processor', compact('formats', 'order'));
    }
}
```

- [ ] **Step 4: Запустити тести**

```bash
php artisan test tests/Feature/Livewire/ --pest
```

Очікуємо: `6 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/PhotoProcessor.php tests/Feature/Livewire/
git commit -m "feat: add PhotoProcessor Livewire component with 3-step flow"
```

---

## Task 8: Blade views + layout

**Files:**
- Create: `app/resources/views/layouts/app.blade.php`
- Create: `app/resources/views/livewire/photo-processor.blade.php`
- Create: `app/routes/web.php`

> Мінімальний адаптивний UI без CSS-фреймворку. Tailwind CSS доступний через CDN.

- [ ] **Step 1: Створити базовий layout**

```html
{{-- app/resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white border-b px-6 py-4">
        <a href="/" class="text-xl font-semibold text-gray-800">📷 Фото на документи</a>
    </header>
    <main class="max-w-2xl mx-auto py-10 px-4">
        @yield('content')
    </main>
    <footer class="text-center text-sm text-gray-400 py-6">
        <a href="{{ route('privacy-policy') }}" class="underline">Політика конфіденційності</a>
    </footer>
    @livewireScripts
</body>
</html>
```

- [ ] **Step 2: Створити Livewire view**

```html
{{-- app/resources/views/livewire/photo-processor.blade.php --}}
<div>
    {{-- КРОК 1: Upload + вибір формату --}}
    @if ($step === 1)
        <h1 class="text-2xl font-bold mb-6">Завантажте фото</h1>

        <form wire:submit="submit" class="space-y-6">
            {{-- Завантаження фото --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Фото</label>
                <input type="file" wire:model="photo" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                              file:border-0 file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('photo') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Вибір формату --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Формат документа</label>
                <select wire:model="documentFormatId"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Оберіть формат —</option>
                    @foreach ($formats as $country => $countryFormats)
                        <optgroup label="{{ $country }}">
                            @foreach ($countryFormats as $format)
                                <option value="{{ $format->id }}">
                                    {{ $format->name }} ({{ $format->width_mm }}×{{ $format->height_mm }}мм)
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('documentFormatId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Privacy Policy --}}
            <div class="flex items-start gap-2">
                <input type="checkbox" wire:model="privacyAccepted" id="privacy" class="mt-1">
                <label for="privacy" class="text-sm text-gray-600">
                    Я погоджуюся з
                    <a href="{{ route('privacy-policy') }}" target="_blank" class="text-blue-600 underline">
                        Політикою конфіденційності
                    </a>
                </label>
            </div>
            @error('privacyAccepted') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <span wire:loading.remove>Обробити фото</span>
                <span wire:loading>Завантаження…</span>
            </button>
        </form>
    @endif

    {{-- КРОК 2: Очікування + прев'ю --}}
    @if ($step === 2)
        <h1 class="text-2xl font-bold mb-6">Ваше фото</h1>

        @if (!$order || in_array($order->status, ['pending', 'processing']))
            {{-- Спінер під час обробки --}}
            <div wire:poll.2000ms="checkStatus" class="text-center py-12">
                <div class="inline-block w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600">Обробляємо фото, зачекайте…</p>
            </div>
        @elseif ($order->status === 'failed')
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <p class="text-red-700 font-semibold">Не вдалось обробити фото</p>
                <p class="text-red-600 text-sm mt-1">Спробуйте інше фото з чітким обличчям</p>
                <button wire:click="$set('step', 1)" class="mt-4 text-blue-600 underline text-sm">
                    Спробувати знову
                </button>
            </div>
        @elseif ($order->status === 'completed')
            {{-- Прев'ю з watermark --}}
            <div class="space-y-6">
                <div class="border rounded-lg overflow-hidden">
                    <img src="{{ route('preview', $order->uuid) }}" alt="Прев'ю фото"
                         class="w-full max-w-xs mx-auto block">
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-gray-800 font-semibold mb-1">Фото готове!</p>
                    <p class="text-gray-600 text-sm mb-4">
                        Завантажте версію без водяного знаку
                    </p>
                    <button wire:click="pay"
                            class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        <span wire:loading.remove wire:target="pay">Завантажити без watermark — безкоштовно</span>
                        <span wire:loading wire:target="pay">Обробка…</span>
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
```

- [ ] **Step 3: Оновити routes/web.php**

```php
<?php
// app/routes/web.php

use App\Http\Controllers\PhotoController;
use App\Livewire\PhotoProcessor;
use Illuminate\Support\Facades\Route;

Route::get('/', PhotoProcessor::class)->name('home');
Route::get('/preview/{uuid}', [PhotoController::class, 'preview'])->name('preview');
Route::get('/result/{uuid}', [PhotoController::class, 'result'])->name('result');
Route::get('/download/{uuid}', [PhotoController::class, 'download'])->name('download');
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
```

- [ ] **Step 4: Перевірити що layout + маршрут `/` рендерить без помилок**

```bash
php artisan route:list
```

Очікуємо: рядки з `/`, `/preview/{uuid}`, `/result/{uuid}`, `/download/{uuid}`, `/privacy-policy`

- [ ] **Step 5: Commit**

```bash
git add resources/views/ routes/web.php
git commit -m "feat: add Blade views and routes for photo processor flow"
```

---

## Task 9: PhotoController

**Files:**
- Create: `app/app/Http/Controllers/PhotoController.php`
- Create: `app/tests/Feature/Http/PhotoControllerTest.php`

- [ ] **Step 1: Написати падаючі тести**

```php
<?php
// app/tests/Feature/Http/PhotoControllerTest.php

use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('preview returns watermark image for valid uuid', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id'   => $format->id,
        'status'               => 'completed',
        'result_watermark_path' => 'results/test_watermark.png',
        'expires_at'           => now()->addHours(24),
    ]);
    Storage::put('results/test_watermark.png', file_get_contents(base_path('tests/fixtures/1x1.png')));

    $this->get(route('preview', $order->uuid))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('preview returns 404 for expired order', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'expires_at'         => now()->subHour(),
    ]);

    $this->get(route('preview', $order->uuid))->assertNotFound();
});

it('download returns clean image when order is paid', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'completed',
        'result_clean_path'  => 'results/test_clean.png',
        'paid_at'            => now(),
        'expires_at'         => now()->addHours(24),
    ]);
    Storage::put('results/test_clean.png', file_get_contents(base_path('tests/fixtures/1x1.png')));

    $this->get(route('download', $order->uuid))
        ->assertOk()
        ->assertDownload('photo.png');
});

it('download returns 404 when order not paid', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'completed',
        'paid_at'            => null,
        'expires_at'         => now()->addHours(24),
    ]);

    $this->get(route('download', $order->uuid))->assertNotFound();
});
```

- [ ] **Step 2: Запустити — переконатись що падають**

```bash
php artisan test tests/Feature/Http/ --pest
```

Очікуємо: `FAILED` — `Class "App\Http\Controllers\PhotoController" not found` або `Route [preview] not defined`

- [ ] **Step 3: Реалізувати PhotoController**

```php
<?php
// app/app/Http/Controllers/PhotoController.php

namespace App\Http\Controllers;

use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    public function preview(string $uuid): \Illuminate\Http\Response
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->whereNotNull('result_watermark_path')
            ->firstOrFail();

        $bytes = Storage::get($order->result_watermark_path);

        return response($bytes, 200, ['Content-Type' => 'image/png']);
    }

    public function result(string $uuid): \Illuminate\View\View
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('pages.result', compact('order'));
    }

    public function download(string $uuid): StreamedResponse
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->whereNotNull('paid_at')
            ->whereNotNull('result_clean_path')
            ->firstOrFail();

        return Storage::download($order->result_clean_path, 'photo.png');
    }
}
```

- [ ] **Step 4: Запустити тести**

```bash
php artisan test tests/Feature/Http/ --pest
```

Очікуємо: `4 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PhotoController.php tests/Feature/Http/
git commit -m "feat: add PhotoController for preview, result and download endpoints"
```

---

## Task 10: Privacy Policy + CleanExpiredOrders

**Files:**
- Create: `app/resources/views/pages/privacy-policy.blade.php`
- Create: `app/app/Console/Commands/CleanExpiredOrders.php`
- Create: `app/tests/Feature/Console/CleanExpiredOrdersTest.php`
- Modify: `app/bootstrap/app.php`

- [ ] **Step 1: Написати падаючий тест для команди**

```php
<?php
// app/tests/Feature/Console/CleanExpiredOrdersTest.php

use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('deletes expired orders and their files', function () {
    $format = DocumentFormat::factory()->create();

    // Прострочений order з файлами
    $expired = PhotoOrder::factory()->create([
        'document_format_id'    => $format->id,
        'original_path'         => 'originals/old.png',
        'result_clean_path'     => 'results/old_clean.png',
        'result_watermark_path' => 'results/old_watermark.png',
        'expires_at'            => now()->subHour(),
    ]);
    Storage::put('originals/old.png', 'bytes');
    Storage::put('results/old_clean.png', 'bytes');
    Storage::put('results/old_watermark.png', 'bytes');

    // Актуальний order
    $active = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/new.png',
        'expires_at'         => now()->addHours(24),
    ]);
    Storage::put('originals/new.png', 'bytes');

    $this->artisan('orders:clean')->assertSuccessful();

    expect(PhotoOrder::find($expired->id))->toBeNull()
        ->and(PhotoOrder::find($active->id))->not->toBeNull();

    Storage::assertMissing('originals/old.png');
    Storage::assertMissing('results/old_clean.png');
    Storage::assertMissing('results/old_watermark.png');
    Storage::assertExists('originals/new.png');
});
```

- [ ] **Step 2: Запустити — переконатись що тест падає**

```bash
php artisan test tests/Feature/Console/ --pest
```

Очікуємо: `FAILED`

- [ ] **Step 3: Створити команду CleanExpiredOrders**

```bash
php artisan make:command CleanExpiredOrders
```

```php
<?php
// app/app/Console/Commands/CleanExpiredOrders.php

namespace App\Console\Commands;

use App\Models\PhotoOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExpiredOrders extends Command
{
    protected $signature   = 'orders:clean';
    protected $description = 'Delete expired photo orders and their files';

    public function handle(): int
    {
        $count = 0;

        PhotoOrder::expired()->chunkById(100, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                foreach ([
                    $order->original_path,
                    $order->result_clean_path,
                    $order->result_watermark_path,
                ] as $path) {
                    if ($path && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                $order->delete();
                $count++;
            }
        });

        $this->info("Deleted {$count} expired orders.");
        return Command::SUCCESS;
    }
}
```

- [ ] **Step 4: Зареєструвати в планувальнику**

У `app/bootstrap/app.php` додати в `withSchedule` (Laravel 11+ синтаксис):

```php
->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
    $schedule->command('orders:clean')->hourly();
})
```

- [ ] **Step 5: Створити Privacy Policy сторінку**

```html
{{-- app/resources/views/pages/privacy-policy.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="prose max-w-none">
    <h1 class="text-2xl font-bold mb-6">Політика конфіденційності</h1>

    <h2 class="text-lg font-semibold mt-6 mb-2">Що ми збираємо</h2>
    <p class="text-gray-700 mb-4">
        При обробці фото ми тимчасово зберігаємо: завантажене фото, результат обробки,
        IP-адресу та технічні дані запиту (User-Agent, cookies сесії).
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Як ми зберігаємо дані</h2>
    <p class="text-gray-700 mb-4">
        Файли зберігаються на захищеному сервері виключно для надання послуги.
        Доступ до файлів здійснюється через унікальне посилання.
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Термін зберігання</h2>
    <p class="text-gray-700 mb-4">
        Всі завантажені фото та результати обробки автоматично видаляються через <strong>24 години</strong>.
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Видалення даних</h2>
    <p class="text-gray-700 mb-4">
        Якщо ви хочете видалити дані раніше, зв'яжіться з нами.
        Після закінчення терміну дані видаляються автоматично.
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Cookies</h2>
    <p class="text-gray-700 mb-4">
        Ми використовуємо сесійні cookies для ідентифікації замовлення.
        Cookies видаляються при закритті браузера або після 24 годин.
    </p>
</div>
@endsection
```

- [ ] **Step 6: Запустити всі тести**

```bash
php artisan test tests/Feature/Console/ --pest
```

Очікуємо: `1 passed`

- [ ] **Step 7: Commit**

```bash
git add app/Console/Commands/ bootstrap/app.php resources/views/pages/
git commit -m "feat: add CleanExpiredOrders command (hourly cron) + Privacy Policy page"
```

---

## Task 11: Filament admin panel

**Files:**
- Create: `app/app/Filament/Resources/DocumentFormatResource.php`
- Create: `app/app/Filament/Pages/StatsPage.php`

> Filament 5 ресурси — стандартний pattern. Якщо API дещо відрізняється від v3, адаптувати відповідно.

- [ ] **Step 1: Згенерувати Filament resource**

```bash
php artisan make:filament-resource DocumentFormat --generate
```

Відкрити `app/Filament/Resources/DocumentFormatResource.php` і налаштувати form та table:

```php
<?php
// app/app/Filament/Resources/DocumentFormatResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentFormatResource\Pages;
use App\Models\DocumentFormat;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentFormatResource extends Resource
{
    protected static ?string $model = DocumentFormat::class;
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationLabel = 'Формати документів';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Назва')->required(),
            TextInput::make('country')->label('Країна (ISO)')->maxLength(2)->required(),
            TextInput::make('width_mm')->label('Ширина, мм')->numeric()->required(),
            TextInput::make('height_mm')->label('Висота, мм')->numeric()->required(),
            TextInput::make('dpi')->label('DPI')->numeric()->default(300)->required(),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
            Checkbox::make('is_active')->label('Активний'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Назва')->sortable()->searchable(),
            TextColumn::make('country')->label('Країна'),
            TextColumn::make('width_mm')->label('Ширина'),
            TextColumn::make('height_mm')->label('Висота'),
            TextColumn::make('dpi')->label('DPI'),
            IconColumn::make('is_active')->label('Активний')->boolean(),
            TextColumn::make('sort_order')->label('Порядок')->sortable(),
        ])
        ->defaultSort('sort_order')
        ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDocumentFormats::route('/'),
            'create' => Pages\CreateDocumentFormat::route('/create'),
            'edit'   => Pages\EditDocumentFormat::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 2: Створити Stats сторінку в Filament**

```bash
php artisan make:filament-page StatsPage
```

```php
<?php
// app/app/Filament/Pages/StatsPage.php

namespace App\Filament\Pages;

use App\Models\PhotoOrder;
use Filament\Pages\Page;

class StatsPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Статистика';
    protected static string  $view            = 'filament.pages.stats-page';

    public function getViewData(): array
    {
        return [
            'total'        => PhotoOrder::count(),
            'paid'         => PhotoOrder::whereNotNull('paid_at')->count(),
            'today'        => PhotoOrder::whereDate('created_at', today())->count(),
            'this_week'    => PhotoOrder::where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month'   => PhotoOrder::where('created_at', '>=', now()->startOfMonth())->count(),
            'failed'       => PhotoOrder::where('status', 'failed')->count(),
        ];
    }
}
```

- [ ] **Step 3: Створити шаблон для Stats сторінки**

```bash
mkdir -p app/resources/views/filament/pages
```

```html
{{-- app/resources/views/filament/pages/stats-page.blade.php --}}
<x-filament-panels::page>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Всього замовлень</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Оплачено</p>
            <p class="text-3xl font-bold text-green-600">{{ $paid }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Сьогодні</p>
            <p class="text-3xl font-bold text-blue-600">{{ $today }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Цього тижня</p>
            <p class="text-3xl font-bold text-gray-700">{{ $this_week }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Цього місяця</p>
            <p class="text-3xl font-bold text-gray-700">{{ $this_month }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Помилок</p>
            <p class="text-3xl font-bold text-red-500">{{ $failed }}</p>
        </div>
    </div>
</x-filament-panels::page>
```

- [ ] **Step 4: Перевірити що Filament доступний**

```bash
php artisan filament:check-panel
```

або просто:

```bash
php artisan serve
```

Відкрити `http://localhost:8000/admin` та перевірити що сторінки `Формати документів` та `Статистика` доступні.

- [ ] **Step 5: Commit**

```bash
git add app/Filament/ resources/views/filament/
git commit -m "feat: add Filament admin panel with DocumentFormat CRUD and Stats page"
```

---

## Task 12: DocumentFormatSeeder + повний запуск

**Files:**
- Create: `app/database/seeders/DocumentFormatSeeder.php`
- Modify: `app/database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Створити seeder з реальними форматами**

```php
<?php
// app/database/seeders/DocumentFormatSeeder.php

namespace Database\Seeders;

use App\Models\DocumentFormat;
use Illuminate\Database\Seeder;

class DocumentFormatSeeder extends Seeder
{
    public function run(): void
    {
        $formats = [
            // Україна
            ['name' => 'Паспорт UA',      'country' => 'UA', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'sort_order' => 10],
            ['name' => 'Закордонний пас.','country' => 'UA', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'sort_order' => 20],
            ['name' => 'ID-картка UA',    'country' => 'UA', 'width_mm' => 25, 'height_mm' => 35, 'dpi' => 300, 'sort_order' => 30],
            // США
            ['name' => 'US Passport',     'country' => 'US', 'width_mm' => 51, 'height_mm' => 51, 'dpi' => 300, 'sort_order' => 40],
            ['name' => 'US Visa',         'country' => 'US', 'width_mm' => 51, 'height_mm' => 51, 'dpi' => 300, 'sort_order' => 50],
            // ЄС
            ['name' => 'EU Passport',     'country' => 'EU', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'sort_order' => 60],
            ['name' => 'Schengen Visa',   'country' => 'EU', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'sort_order' => 70],
        ];

        foreach ($formats as $data) {
            DocumentFormat::updateOrCreate(
                ['name' => $data['name'], 'country' => $data['country']],
                $data + ['is_active' => true]
            );
        }
    }
}
```

- [ ] **Step 2: Додати в DatabaseSeeder**

```php
// app/database/seeders/DatabaseSeeder.php — в методі run():
$this->call(DocumentFormatSeeder::class);
```

- [ ] **Step 3: Запустити seed та всі тести**

```bash
php artisan db:seed
php artisan test --pest
```

Очікуємо: всі тести `passed`, 7 форматів у таблиці.

- [ ] **Step 4: Перевірити docker-compose збірку**

```bash
cd /Users/ernestbehinov/Work/photo-processor
docker compose build app worker
```

Очікуємо: `Successfully built` без помилок.

- [ ] **Step 5: Запустити всі сервіси**

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Відкрити `http://localhost` — має з'явитись форма завантаження фото.

- [ ] **Step 6: Smoke-тест повного флоу**

1. Завантажити фото з `test-photos/` через браузер
2. Обрати формат "Паспорт UA"
3. Натиснути "Обробити"
4. Дочекатись прев'ю з watermark (polling кожні 2 сек)
5. Натиснути "Завантажити" — отримати чисте PNG

- [ ] **Step 7: Final commit**

```bash
git add database/seeders/
git commit -m "feat: add DocumentFormatSeeder with real passport/visa formats"
```

---

## Self-Review

### Spec coverage

| Вимога специфікації | Задача |
|---------------------|--------|
| FastAPI width_mm/height_mm/dpi params | Task 1 |
| Laravel + Docker Compose (app/worker/redis/processor) | Task 2 |
| document_formats + photo_orders таблиці | Task 3 |
| PhotoProcessorClient HTTP | Task 4 |
| Watermark через GD | Task 5 |
| ProcessPhotoJob (clean + watermark) | Task 6 |
| Livewire 3-step flow (upload/polling/download) | Task 7 |
| Routes + PhotoController (preview/result/download) | Tasks 8-9 |
| Privacy Policy сторінка + чекбокс | Task 8, 10 |
| Auto-cleanup cron через 24h | Task 10 |
| Filament CRUD DocumentFormat | Task 11 |
| Filament статистика | Task 11 |
| Seeder з реальними форматами | Task 12 |
| Payment stub (paid_at) | Task 7 (pay method) |
| UUID-based order URL | Task 3, 7 |

### Gaps: немає

### Type consistency: перевірено

- `PhotoOrder::expired()` scope використовується в Task 10 ✓
- `DocumentFormat::active()` scope використовується в Task 7 ✓
- `PhotoProcessorClient::process($bytes, $w, $h, $dpi)` → Task 6 ✓
- `WatermarkService::apply($bytes)` → Task 6 ✓
