# Posyandu API

Backend API untuk Website Posyandu Loa Duri Ulu.

## Tech Stack

- Laravel 13
- MySQL 8
- Docker

---

## Menjalankan dengan Docker

Clone repository:

```bash
git clone <repository-url>
cd posyandu-api
```

Copy environment:

```bash
cp .env.example .env
```

Build dan jalankan container:

```bash
docker compose up -d --build
```

Generate key Laravel:

```bash
docker compose exec laravel php artisan key:generate
```

Jalankan migration dan seeder:

```bash
docker compose exec laravel php artisan migrate --seed
```

API dapat diakses di:

```text
http://localhost:8000
```

---

## Menjalankan Tanpa Docker

Pastikan sudah terinstall:

- PHP 8.4+
- Composer
- MySQL

Copy environment:

```bash
cp .env.example .env
```

Sesuaikan database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=posyandu_db
DB_USERNAME=root
DB_PASSWORD=
```

Install dependency:

```bash
composer install
```

Generate key:

```bash
php artisan key:generate
```

Migration dan seeder:

```bash
php artisan migrate --seed
```

Jalankan server:

```bash
php artisan serve
```

---


## Catatan

Jika terjadi perubahan struktur database:

```bash
php artisan migrate
```

Jika ingin mengulang database dari awal:

```bash
php artisan migrate:fresh --seed
```
