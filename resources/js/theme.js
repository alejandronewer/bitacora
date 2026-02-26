import { fetchConfiguracionSistema, fetchConfiguracionUsuario } from './api/configuracion';

const STORAGE_KEY = 'bitacora.theme';
const DENSITY_KEY = 'bitacora.density';
const DENSITY_CLASS = {
  compacta: 'density-compact',
  normal: 'density-normal',
  grande: 'density-grande',
};

export const getStoredTheme = () => {
  try {
    return localStorage.getItem(STORAGE_KEY);
  } catch {
    return null;
  }
};

export const clearStoredTheme = () => {
  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch {
    // ignore
  }
};

export const applyThemeValue = (value, { persist = true } = {}) => {
  const html = document.documentElement;
  const body = document.body;
  const app = document.getElementById('app');
  const theme = value === 'light' ? 'light' : 'dark';

  if (theme === 'light') {
    html.classList.remove('dark');
    body.classList.remove('dark');
    app?.classList.remove('dark');
  } else {
    html.classList.add('dark');
    body.classList.add('dark');
    app?.classList.add('dark');
  }

  if (persist) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch {
      // ignore
    }
  }
};

export const getStoredDensity = () => {
  try {
    return localStorage.getItem(DENSITY_KEY);
  } catch {
    return null;
  }
};

export const clearStoredDensity = () => {
  try {
    localStorage.removeItem(DENSITY_KEY);
  } catch {
    // ignore
  }
};

export const clearStoredUiPrefs = () => {
  clearStoredTheme();
  clearStoredDensity();
};

export const applyDensityValue = (value, { persist = true } = {}) => {
  const html = document.documentElement;
  const body = document.body;
  const app = document.getElementById('app');
  const density = DENSITY_CLASS[value] ? value : 'normal';
  const className = DENSITY_CLASS[density];

  Object.values(DENSITY_CLASS).forEach((cls) => {
    html.classList.remove(cls);
    body.classList.remove(cls);
    app?.classList.remove(cls);
  });

  html.classList.add(className);
  body.classList.add(className);
  app?.classList.add(className);

  if (persist) {
    try {
      localStorage.setItem(DENSITY_KEY, density);
    } catch {
      // ignore
    }
  }
};

export const initTheme = async () => {
  const stored = getStoredTheme();
  if (stored) {
    applyThemeValue(stored, { persist: false });
  }

  const storedDensity = getStoredDensity();
  if (storedDensity) {
    applyDensityValue(storedDensity, { persist: false });
  }

  try {
    const config = await fetchConfiguracionSistema();
    const theme = Array.isArray(config)
      ? config.find((item) => item.clave === 'tema.modo')?.valor
      : null;

    if (theme) {
      applyThemeValue(theme);
    }
  } catch {
    // ignore
  }

  try {
    const userConfig = await fetchConfiguracionUsuario({ skipAuthRedirect: true });
    if (Array.isArray(userConfig)) {
      const userTheme = userConfig.find((item) => item.clave === 'tema.modo')?.valor;
      const userDensity = userConfig.find((item) => item.clave === 'ui.densidad')?.valor;

      if (userTheme && userTheme !== 'global') {
        applyThemeValue(userTheme);
      }
      if (userDensity) {
        applyDensityValue(userDensity);
      }
    }
  } catch {
    // ignore
  }

  if (!stored) {
    applyThemeValue('dark', { persist: false });
  }

  if (!storedDensity) {
    applyDensityValue('normal', { persist: false });
  }
};
