import { useEffect, useState } from 'react';
import { apiFetch } from './api';

// The allowed categories come from the API (App\Enums\FeedbackCategory).
export function useCategories() {
  const [categories, setCategories] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => {
    apiFetch('/feedback/categories')
      .then(setCategories)
      .catch(() => setError('Could not load categories. Please refresh the page.'));
  }, []);

  return { categories, error };
}
