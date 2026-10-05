import { useState } from 'react';
import { apiFetch } from '../api';

const CATEGORIES = ['general', 'support', 'product', 'bug'];
const EMPTY = { name: '', email: '', rating: '', category: '', comment: '' };

function validate(values) {
  const errors = {};

  if (!values.name.trim()) errors.name = 'Name is required.';

  if (!values.email.trim()) {
    errors.email = 'Email is required.';
  } else if (!/^\S+@\S+\.\S+$/.test(values.email)) {
    errors.email = 'Enter a valid email address.';
  }

  if (!values.rating) errors.rating = 'Choose a rating.';
  if (!values.category) errors.category = 'Choose a category.';

  return errors;
}

function FeedbackForm() {
  const [values, setValues] = useState(EMPTY);
  const [errors, setErrors] = useState({});
  const [status, setStatus] = useState({ type: '', message: '' });
  const [sending, setSending] = useState(false);

  function handleChange(event) {
    const { name, value } = event.target;
    setValues((current) => ({ ...current, [name]: value }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setStatus({ type: '', message: '' });

    const found = validate(values);
    setErrors(found);
    if (Object.keys(found).length > 0) return;

    setSending(true);
    try {
      await apiFetch('/feedback', {
        method: 'POST',
        body: JSON.stringify({
          ...values,
          rating: Number(values.rating),
          comment: values.comment.trim() || null,
        }),
      });
      setValues(EMPTY);
      setStatus({ type: 'success', message: 'Thank you. Your feedback was sent.' });
    } catch (error) {
      if (error.status === 422) {
        const serverErrors = {};
        for (const [field, messages] of Object.entries(error.errors)) {
          serverErrors[field] = messages[0];
        }
        setErrors(serverErrors);
        setStatus({ type: 'error', message: 'Please fix the highlighted fields.' });
      } else {
        setStatus({ type: 'error', message: 'Could not send your feedback. Please try again.' });
      }
    } finally {
      setSending(false);
    }
  }

  return (
    <>
      <h1>Submit feedback</h1>

      {status.message && (
        <p className={`alert alert-${status.type}`} role="status">
          {status.message}
        </p>
      )}

      <form className="form" onSubmit={handleSubmit} noValidate>
        <div className="field">
          <label htmlFor="name">Name</label>
          <input
            id="name"
            name="name"
            value={values.name}
            onChange={handleChange}
            className={errors.name ? 'invalid' : ''}
          />
          {errors.name && <p className="field-error">{errors.name}</p>}
        </div>

        <div className="field">
          <label htmlFor="email">Email</label>
          <input
            id="email"
            name="email"
            type="email"
            value={values.email}
            onChange={handleChange}
            className={errors.email ? 'invalid' : ''}
          />
          {errors.email && <p className="field-error">{errors.email}</p>}
        </div>

        <div className="field">
          <label htmlFor="rating">Rating</label>
          <select
            id="rating"
            name="rating"
            value={values.rating}
            onChange={handleChange}
            className={errors.rating ? 'invalid' : ''}
          >
            <option value="">Choose a rating</option>
            {[1, 2, 3, 4, 5].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
          {errors.rating && <p className="field-error">{errors.rating}</p>}
        </div>

        <div className="field">
          <label htmlFor="category">Category</label>
          <select
            id="category"
            name="category"
            value={values.category}
            onChange={handleChange}
            className={errors.category ? 'invalid' : ''}
          >
            <option value="">Choose a category</option>
            {CATEGORIES.map((category) => (
              <option key={category} value={category}>
                {category}
              </option>
            ))}
          </select>
          {errors.category && <p className="field-error">{errors.category}</p>}
        </div>

        <div className="field">
          <label htmlFor="comment">Comment (optional)</label>
          <textarea
            id="comment"
            name="comment"
            rows="4"
            value={values.comment}
            onChange={handleChange}
          />
        </div>

        <button className="button" type="submit" disabled={sending}>
          {sending ? 'Sending...' : 'Send feedback'}
        </button>
      </form>
    </>
  );
}

export default FeedbackForm;