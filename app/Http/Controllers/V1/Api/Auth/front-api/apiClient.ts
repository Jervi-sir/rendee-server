// @ts-nocheck
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';


const getApiBaseUrl = () => {
  const explicitUrl = process.env.EXPO_PUBLIC_API_URL?.trim();

  if (explicitUrl) {
    return explicitUrl.replace(/\/$/, '');
  }

  // Quick toggle: "192.168.1.105" or "rendee.jervi.dev"
  const host: string = 'rendee.jervi.dev';
  // const host: string = "192.168.1.106";

  if (host === 'rendee.jervi.dev') {
    return `https://${host}/api/v1`;
  }

  if (host) {
    return `http://${host}:8000/api/v1`;
  }

  return 'http://127.0.0.1:8000/api/v1';
};

export const AUTH_TOKEN_STORAGE_KEY = 'linked.auth.token';
export const API_BASE_URL = getApiBaseUrl();

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use(async (config) => {
  const authHeader = config.headers?.Authorization;
  if (authHeader) {
    return config;
  }

  const token = await AsyncStorage.getItem(AUTH_TOKEN_STORAGE_KEY);

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

// Helper to set authorization token in header
export const setAuthToken = (token: string | null) => {
  if (token) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    delete apiClient.defaults.headers.common['Authorization'];
  }
};

apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
      setAuthToken(null);
    }

    return Promise.reject(error);
  },
);

export default apiClient;
