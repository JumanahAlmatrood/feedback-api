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
| GET | `/api/feedback/export` | Download all feedback as a CSV file, no emails. **Requires a token.** Limited to 10 requests per minute |
| GET | `/api/feedback/{id}` | Get a single feedback entry |
| DELETE | `/api/feedback/{id}` | Delete a feedback entry. **Requires a token.** |

### POST body

| Field | Rules |
|---|---|
| name | required, text, up to 100 characters |
| email | required, valid email, up to 254 characters |
| rating | required, whole number from 1 to 5 |
| category | required, one of: `general`, `support`, `product`, `bug` |
| comment | optional, text, up to 1000 characters |

Emails are stored for follow-up but are never returned by any endpoint.

## Authentication

Deleting feedback and exporting the CSV are for staff only. Both need a Sanctum token. Without one they return `401`.

Create a staff user and a token:

```bash
php artisan tinker --execute='echo App\Models\User::factory()->create()->createToken("local")->plainTextToken;'
```

Send the token in the `Authorization` header:

```bash
curl -X DELETE http://127.0.0.1:8000/api/feedback/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Submitting feedback, the list, a single entry, stats and categories stay public.

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

A Postman collection is in the `postman` folder. Import it into Postman and run the requests while the server is running. The delete and export requests need a token (see Authentication).

## Notes

- The allowed categories are defined once in `app/Enums/FeedbackCategory.php`. The frontend loads them from `/api/feedback/categories`, so adding a category only needs a change in the enum.
- Input validation lives in Form Requests under `app/Http/Requests`.
- `php artisan db:seed` (or `migrate --seed`) creates 50 fake feedback entries.
- In the CSV export, values starting with `=`, `+`, `-` or `@` are prefixed with `'` so spreadsheet apps do not run them as formulas.
- The list and stats endpoints are still public. In production they would also be restricted to staff, and `APP_DEBUG` must be `false`.
