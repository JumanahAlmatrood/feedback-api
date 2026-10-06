# Feedback API

A Laravel REST API for collecting feedback, with a React frontend for submitting feedback, browsing it, and viewing insights.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 20 or newer

## Setup

```bash
git clone https://github.com/JumanahAlmatrood/feedback-api.git
cd feedback-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

When `migrate` asks to create the SQLite database, answer `yes`.

The API runs at `http://127.0.0.1:8000`.

## Endpoints

| Method | URL | Description |
|---|---|---|
| POST | `/api/feedback` | Submit new feedback (limited to 10 requests per minute) |
| GET | `/api/feedback` | List feedback, 15 per page (`?page=2`). Optional filters: `?category=bug&rating=5` |
| GET | `/api/feedback/categories` | The list of allowed categories |
| GET | `/api/feedback/stats` | Total count, average rating, rating distribution, and count per category |
| GET | `/api/feedback/{id}` | Get a single feedback entry |
| DELETE | `/api/feedback/{id}` | Delete a feedback entry |

### POST body

| Field | Rules |
|---|---|
| name | required, text, up to 100 characters |
| email | required, valid email, up to 254 characters |
| rating | required, whole number from 1 to 5 |
| category | required, one of: `general`, `support`, `product`, `bug` |
| comment | optional, text, up to 1000 characters |

Emails are stored for follow-up but are never returned by any endpoint.

## Frontend

The React app is in the `frontend` folder. The Laravel server must be running at the same time.

```bash
cd frontend
npm install
npm run dev
```

Open `http://localhost:5173`.

| Page | What it does |
|---|---|
| Submit | Feedback form with client-side validation and success/error messages |
| Feedback | Feedback list with filters by category and rating |
| Insights | Total count, average rating, rating distribution chart, and count per category |

The stats are calculated in the database by the `/api/feedback/stats` endpoint, not in the frontend.

## Testing

```bash
php artisan test
```

A Postman collection is in the `postman` folder. Import it into Postman and run the requests while the server is running.

## Notes

- The allowed categories are defined once in `app/Enums/FeedbackCategory.php`. The frontend loads them from `/api/feedback/categories`, so adding a category only needs a change in the enum.
- `php artisan db:seed` (or `migrate --seed`) creates 50 fake feedback entries.
