import { fetchMe } from './api/me';

let cachedUser = null;
let lastFetchAt = 0;
let inFlight = null;

const AUTH_CACHE_MS = 60 * 1000;

export const getAuthUser = async ({ force = false } = {}) => {
  const now = Date.now();
  if (!force && cachedUser && now - lastFetchAt < AUTH_CACHE_MS) {
    return cachedUser;
  }

  if (inFlight) {
    return inFlight;
  }

  inFlight = fetchMe()
    .then((user) => {
      cachedUser = user;
      lastFetchAt = Date.now();
      return cachedUser;
    })
    .catch(() => {
      cachedUser = null;
      lastFetchAt = Date.now();
      return null;
    })
    .finally(() => {
      inFlight = null;
    });

  return inFlight;
};

export const clearAuthUser = () => {
  cachedUser = null;
  lastFetchAt = 0;
};

export const peekAuthUser = () => cachedUser;
