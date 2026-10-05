import { useEffect, useState } from 'react';
import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { apiFetch } from '../api';

function Insights() {
  const [stats, setStats] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    apiFetch('/feedback/stats')
      .then(setStats)
      .catch(() => setError('Could not load insights. Please try again.'));
  }, []);

  if (error) {
    return (
      <>
        <h1>Insights</h1>
        <p className="alert alert-error">{error}</p>
      </>
    );
  }

  if (!stats) {
    return (
      <>
        <h1>Insights</h1>
        <p className="muted">Loading...</p>
      </>
    );
  }

  return (
    <>
      <h1>Insights</h1>

      <div className="stat-grid">
        <div className="stat-card">
          <p className="stat-label">Total feedback</p>
          <p className="stat-value">{stats.total}</p>
        </div>
        <div className="stat-card">
          <p className="stat-label">Average rating</p>
          <p className="stat-value">
            {stats.average_rating} <span className="stat-unit">/ 5</span>
          </p>
        </div>
      </div>

      <section className="panel">
        <h2>Rating distribution</h2>
        <div className="chart">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={stats.rating_distribution}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis dataKey="rating" />
              <YAxis allowDecimals={false} />
              <Tooltip />
              <Bar dataKey="count" fill="#2f5bd7" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </section>

      <section className="panel">
        <h2>Feedback per category</h2>
        <table className="table">
          <thead>
            <tr>
              <th>Category</th>
              <th>Count</th>
            </tr>
          </thead>
          <tbody>
            {stats.by_category.map((row) => (
              <tr key={row.category}>
                <td>{row.category}</td>
                <td>{row.count}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </>
  );
}

export default Insights;