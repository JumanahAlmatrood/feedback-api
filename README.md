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
| GET | `/api/feedback` | List feedback, 15 per page (`?page=2`). Optional filters: `?category=bug&rating=5` |
| GET | `/api/feedback/stats` | Total count, average rating, rating distribution, and count per category |
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

A Postman collection is in the `postman` folder. Import it into Postman and run the requests while the server is running.

## Notes

- The task did not list allowed values for `category`, so the API accepts any text. The form offers four: general, support, product, bug.
- The seeder creates 50 fake entries for testing.
