import { useEffect, useState } from 'react';
import { apiFetch } from '../api';
import { useCategories } from '../categories';

function FeedbackList() {
  const [filters, setFilters] = useState({ category: '', rating: '' });
  const [page, setPage] = useState(1);
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');
  const { categories, error: categoriesError } = useCategories();

  useEffect(() => {
    const params = new URLSearchParams({ page });
    if (filters.category) params.set('category', filters.category);
    if (filters.rating) params.set('rating', filters.rating);

    let ignore = false;

    apiFetch(`/feedback?${params}`)
      .then((data) => {
        if (ignore) return;
        setResult(data);
        setError('');
      })
      .catch(() => {
        if (!ignore) setError('Could not load feedback. Please try again.');
      });

    return () => {
      ignore = true;
    };
  }, [filters, page]);

  function handleFilterChange(event) {
    const { name, value } = event.target;
    setFilters((current) => ({ ...current, [name]: value }));
    setPage(1);
  }

  return (
    <>
      <h1>All feedback</h1>

      <div className="filters">
        <div className="field">
          <label htmlFor="filter-category">Category</label>
          <select
            id="filter-category"
            name="category"
            value={filters.category}
            onChange={handleFilterChange}
          >
            <option value="">All categories</option>
            {categories.map((category) => (
              <option key={category} value={category}>
                {category}
              </option>
            ))}
          </select>
        </div>

        <div className="field">
          <label htmlFor="filter-rating">Rating</label>
          <select
            id="filter-rating"
            name="rating"
            value={filters.rating}
            onChange={handleFilterChange}
          >
            <option value="">All ratings</option>
            {[5, 4, 3, 2, 1].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
        </div>
      </div>

      {categoriesError && <p className="alert alert-error">{categoriesError}</p>}

      {error && <p className="alert alert-error">{error}</p>}

      {!error && !result && <p className="muted">Loading...</p>}

      {!error && result && result.data.length === 0 && (
        <p className="muted">No feedback matches these filters.</p>
      )}

      {!error && result && result.data.length > 0 && (
        <>
          <p className="muted">
            Showing {result.from}–{result.to} of {result.total}
          </p>

          <ul className="feedback-list">
            {result.data.map((item) => (
              <li key={item.id} className="feedback-item">
                <div className="feedback-head">
                  <strong>{item.name}</strong>
                  <span className="badge">{item.rating} / 5</span>
                  <span className="badge badge-muted">{item.category}</span>
                </div>
                {item.comment && <p>{item.comment}</p>}
              </li>
            ))}
          </ul>

          <div className="pager">
            <button
              className="button"
              onClick={() => setPage(page - 1)}
              disabled={page <= 1}
            >
              Previous
            </button>
            <span>
              Page {result.current_page} of {result.last_page}
            </span>
            <button
              className="button"
              onClick={() => setPage(page + 1)}
              disabled={page >= result.last_page}
            >
              Next
            </button>
          </div>
        </>
      )}
    </>
  );
}

export default FeedbackList;