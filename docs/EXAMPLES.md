# Integration Examples

This document provides practical examples of integrating `ra-devs/jwt-auth` with frontend frameworks.

## Table of Contents

- [Vue.js Integration](#vuejs-integration)
- [React Integration](#react-integration)
- [Axios Interceptors](#axios-interceptors)
- [Token Refresh Strategy](#token-refresh-strategy)
- [Error Handling](#error-handling)

---

## Vue.js Integration

### 1. API Service Setup

```javascript
// src/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://api.example.com/api',
  withCredentials: true, // Important for refresh token cookies
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// Request interceptor to add access token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('access_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor for token refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // If 401 and not already retried, try to refresh token
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const { data } = await axios.post(
          'https://api.example.com/api/auth/refresh',
          {},
          { withCredentials: true }
        );

        const newToken = data.data.token.access_token;
        localStorage.setItem('access_token', newToken);

        // Retry original request with new token
        originalRequest.headers.Authorization = `Bearer ${newToken}`;
        return api(originalRequest);
      } catch (refreshError) {
        // Refresh failed, logout user
        localStorage.removeItem('access_token');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default api;
```

### 2. Authentication Store (Pinia)

```javascript
// src/stores/auth.js
import { defineStore } from 'pinia';
import api from '@/services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('access_token'),
    isAuthenticated: !!localStorage.getItem('access_token'),
  }),

  actions: {
    async login(email, password) {
      try {
        const { data } = await api.post('/auth/login', { email, password });

        this.token = data.data.token.access_token;
        this.user = data.data.user;
        this.isAuthenticated = true;

        localStorage.setItem('access_token', this.token);

        return { success: true };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Login failed',
          errorCode: error.response?.data?.data?.error_code,
        };
      }
    },

    async register(userData) {
      try {
        const { data } = await api.post('/auth/register', userData);
        return { success: true, user: data.data.user };
      } catch (error) {
        return {
          success: false,
          errors: error.response?.data?.errors || {},
          message: error.response?.data?.message,
        };
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        localStorage.removeItem('access_token');
      }
    },

    async fetchUser() {
      try {
        const { data } = await api.get('/auth/me');
        this.user = data.data.user;
        return { success: true };
      } catch (error) {
        this.logout();
        return { success: false };
      }
    },

    async forgotPassword(email) {
      try {
        const { data } = await api.post('/auth/forgot-password', { email });
        return { success: true, message: data.message };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Request failed',
        };
      }
    },

    async resetPassword(email, code, password, password_confirmation) {
      try {
        const { data } = await api.post('/auth/reset-password', {
          email,
          code,
          password,
          password_confirmation,
        });
        return { success: true, message: data.message };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Reset failed',
          errorCode: error.response?.data?.data?.error_code,
        };
      }
    },
  },
});
```

### 3. Login Component

```vue
<!-- src/components/LoginForm.vue -->
<template>
  <div class="login-form">
    <h2>Login</h2>

    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          :disabled="loading"
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          :disabled="loading"
        />
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Logging in...' : 'Login' }}
      </button>
    </form>

    <router-link to="/forgot-password">Forgot Password?</router-link>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  const result = await authStore.login(form.value.email, form.value.password);

  if (result.success) {
    await authStore.fetchUser();
    router.push('/dashboard');
  } else {
    error.value = result.message;
  }

  loading.value = false;
};
</script>
```

---

## React Integration

### 1. API Service Setup

```javascript
// src/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://api.example.com/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('access_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor for auto-refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const { data } = await axios.post(
          'https://api.example.com/api/auth/refresh',
          {},
          { withCredentials: true }
        );

        const newToken = data.data.token.access_token;
        localStorage.setItem('access_token', newToken);

        originalRequest.headers.Authorization = `Bearer ${newToken}`;
        return api(originalRequest);
      } catch (refreshError) {
        localStorage.removeItem('access_token');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default api;
```

### 2. Auth Context

```javascript
// src/contexts/AuthContext.jsx
import React, { createContext, useState, useContext, useEffect } from 'react';
import api from '../services/api';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    const token = localStorage.getItem('access_token');

    if (!token) {
      setLoading(false);
      return;
    }

    try {
      const { data } = await api.get('/auth/me');
      setUser(data.data.user);
    } catch (error) {
      localStorage.removeItem('access_token');
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const { data } = await api.post('/auth/login', { email, password });

      localStorage.setItem('access_token', data.data.token.access_token);
      setUser(data.data.user);

      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
      };
    }
  };

  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('access_token');
      setUser(null);
    }
  };

  const register = async (userData) => {
    try {
      const { data } = await api.post('/auth/register', userData);
      return { success: true, user: data.data.user };
    } catch (error) {
      return {
        success: false,
        errors: error.response?.data?.errors || {},
      };
    }
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, register }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

### 3. Login Component

```javascript
// src/components/LoginForm.jsx
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export const LoginForm = () => {
  const navigate = useNavigate();
  const { login } = useAuth();

  const [form, setForm] = useState({ email: '', password: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    const result = await login(form.email, form.password);

    if (result.success) {
      navigate('/dashboard');
    } else {
      setError(result.message);
    }

    setLoading(false);
  };

  return (
    <div className="login-form">
      <h2>Login</h2>

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="email">Email</label>
          <input
            id="email"
            type="email"
            value={form.email}
            onChange={(e) => setForm({ ...form, email: e.target.value })}
            required
            disabled={loading}
          />
        </div>

        <div className="form-group">
          <label htmlFor="password">Password</label>
          <input
            id="password"
            type="password"
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
            required
            disabled={loading}
          />
        </div>

        {error && <div className="error-message">{error}</div>}

        <button type="submit" disabled={loading}>
          {loading ? 'Logging in...' : 'Login'}
        </button>
      </form>
    </div>
  );
};
```

---

## Token Refresh Strategy

### Proactive Token Refresh

Instead of waiting for 401 errors, refresh the token proactively before it expires:

```javascript
// src/services/tokenRefresh.js
let refreshInterval = null;

export const startTokenRefresh = (expiresIn) => {
  stopTokenRefresh();

  // Refresh 5 minutes before expiry
  const refreshTime = (expiresIn - 300) * 1000;

  refreshInterval = setInterval(async () => {
    try {
      const { data } = await axios.post(
        'https://api.example.com/api/auth/refresh',
        {},
        { withCredentials: true }
      );

      const newToken = data.data.token.access_token;
      localStorage.setItem('access_token', newToken);

      // Restart interval with new expiry time
      startTokenRefresh(data.data.token.expires_in);
    } catch (error) {
      console.error('Token refresh failed:', error);
      stopTokenRefresh();
      window.location.href = '/login';
    }
  }, refreshTime);
};

export const stopTokenRefresh = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};
```

Use it after login:

```javascript
const result = await authStore.login(email, password);
if (result.success) {
  // Start automatic token refresh
  startTokenRefresh(result.expiresIn);
}
```

---

## Error Handling

### Centralized Error Handler

```javascript
// src/utils/errorHandler.js
export const handleApiError = (error) => {
  const response = error.response;

  if (!response) {
    return {
      message: 'Network error. Please check your connection.',
      code: 'NETWORK_ERROR',
    };
  }

  const { status, data } = response;
  const errorCode = data?.data?.error_code;

  switch (errorCode) {
    case 'INVALID_CREDENTIALS':
      return { message: 'Invalid email or password', code: errorCode };

    case 'USER_NOT_FOUND':
      return { message: 'User not found', code: errorCode };

    case 'INVALID_TOKEN':
      return { message: 'Session expired. Please login again', code: errorCode };

    case 'PASSWORD_RESET_CODE_EXPIRED':
      return { message: 'Reset code has expired', code: errorCode };

    case 'PASSWORD_RESET_CODE_INVALID':
      return { message: 'Invalid reset code', code: errorCode };

    case 'PASSWORD_RESET_TOO_MANY_ATTEMPTS':
      return { message: 'Too many attempts. Request a new code', code: errorCode };

    case 'RATE_LIMIT_EXCEEDED':
      return { message: 'Too many requests. Please try again later', code: errorCode };

    default:
      return {
        message: data?.message || 'An error occurred',
        code: errorCode || 'UNKNOWN_ERROR',
      };
  }
};
```

Usage:

```javascript
try {
  await api.post('/auth/login', credentials);
} catch (error) {
  const { message, code } = handleApiError(error);
  showNotification(message, 'error');

  // Handle specific errors
  if (code === 'RATE_LIMIT_EXCEEDED') {
    // Disable form for a while
  }
}
```

---

## Best Practices

1. **Always use `withCredentials: true`** for API requests to include refresh token cookies
2. **Store access tokens in localStorage**, not in cookies (prevents CSRF)
3. **Implement automatic token refresh** before expiration
4. **Handle 401 errors globally** with axios interceptors
5. **Clear tokens on logout** from both localStorage and server-side
6. **Use error codes** for programmatic error handling
7. **Implement rate limiting feedback** in the UI
8. **Show generic messages** for security-sensitive endpoints (e.g., forgot password)
