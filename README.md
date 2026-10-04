# Feedback API

A simple REST API for collecting feedback, built with Laravel.

## Requirements

- PHP 8.3 or newer
- Composer

## Setup

```bash
git clone https://github.com/JumanahAlmatrood/feedback-api.git
cd feedback-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=FeedbackSeeder
php artisan serve
```

When `migrate` asks to create the SQLite database, answer `yes`.

The API runs at `http://127.0.0.1:8000`.

## Endpoints

| Method | URL | Description |
|---|---|---|
| POST | `/api/feedback` | Submit new feedback |
| GET | `/api/feedback` | List feedback, 15 per page (`?page=2`) |
| GET | `/api/feedback/{id}` | Get a single feedback entry |
| DELETE | `/api/feedback/{id}` | Delete a feedback entry |

### POST body

| Field | Rules |
|---|---|
| name | required, text |
| email | required, valid email |
| rating | required, whole number from 1 to 5 |
| category | required, text |
| comment | optional, text |

## Testing

A Postman collection is in the `postman` folder. Import it into Postman and run the requests while the server is running.

## Notes

- The task did not list allowed values for `category`, so it accepts any text.
- The seeder creates 50 fake entries for testing.
