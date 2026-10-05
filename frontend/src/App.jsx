import { useEffect, useState } from 'react';
import { apiFetch } from './api';

function App() {
  const [total, setTotal] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    apiFetch('/feedback')
      .then((data) => setTotal(data.total))
      .catch((err) => setError(err.message));
  }, []);

  if (error) return <p>Could not reach the API: {error}</p>;
  if (total === null) return <p>Loading...</p>;

  return <p>Connected. The API has {total} feedback entries.</p>;
}

export default App;