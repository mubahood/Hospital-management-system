import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import AuthProvider, { useAuth } from './contexts/AuthContext';
import ThemeProvider from './contexts/ThemeContext';
import Login from './pages/auth/Login';
import Dashboard from './pages/Dashboard';
import EventList from './pages/events/EventList';
import EventCreate from './pages/events/EventCreate';
import EventEdit from './pages/events/EventEdit';
import EventShow from './pages/events/EventShow';
import Layout from './components/Layout';
import LoadingSpinner from './components/LoadingSpinner';

function PrivateRoute({ children }) {
  const { user, loading } = useAuth();
  
  if (loading) {
    return <LoadingSpinner />;
  }
  
  return user ? children : <Navigate to="/app/login" replace />;
}

function PublicRoute({ children }) {
  const { user, loading } = useAuth();
  
  if (loading) {
    return <LoadingSpinner />;
  }
  
  return user ? <Navigate to="/app/dashboard" replace /> : children;
}

function AppRoutes() {
  return (
    <Router>
      <Routes>
        {/* Public routes */}
        <Route path="/app/login" element={
          <PublicRoute>
            <Login />
          </PublicRoute>
        } />
        
        {/* Private routes */}
        <Route path="/app/*" element={
          <PrivateRoute>
            <Layout>
              <Routes>
                <Route path="dashboard" element={<Dashboard />} />
                <Route path="events" element={<EventList />} />
                <Route path="events/create" element={<EventCreate />} />
                <Route path="events/:id" element={<EventShow />} />
                <Route path="events/:id/edit" element={<EventEdit />} />
                <Route path="/" element={<Navigate to="/app/dashboard" replace />} />
              </Routes>
            </Layout>
          </PrivateRoute>
        } />
        
        {/* Default redirect */}
        <Route path="/" element={<Navigate to="/app/dashboard" replace />} />
      </Routes>
    </Router>
  );
}

function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <div className="app">
          <AppRoutes />
        </div>
      </AuthProvider>
    </ThemeProvider>
  );
}

export default App;
