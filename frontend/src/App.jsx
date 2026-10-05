import { BrowserRouter, NavLink, Route, Routes } from 'react-router-dom';
import FeedbackForm from './pages/FeedbackForm';
import FeedbackList from './pages/FeedbackList';
import Insights from './pages/Insights';
import './App.css';

function App() {
  return (
    <BrowserRouter>
      <nav className="nav">
        <NavLink to="/">Submit</NavLink>
        <NavLink to="/feedback">Feedback</NavLink>
        <NavLink to="/insights">Insights</NavLink>
      </nav>

      <main className="container">
        <Routes>
          <Route path="/" element={<FeedbackForm />} />
          <Route path="/feedback" element={<FeedbackList />} />
          <Route path="/insights" element={<Insights />} />
        </Routes>
      </main>
    </BrowserRouter>
  );
}

export default App;