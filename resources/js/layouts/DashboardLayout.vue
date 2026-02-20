<template>
  <div class="flex h-screen overflow-hidden bg-background-light dark:bg-background-dark text-slate-900 dark:text-white">
    <SidebarNav :user="user" :app-siglas="appSiglas" :on-logout="logout" :user-icon="userIcon" />

    <main class="flex-1 flex flex-col overflow-hidden">
      <TopBar :user="user" :help-url="helpUrl" :app-name="appName" :user-ready="userReady" />
      <div class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-8">
        <div class="max-w-6xl mx-auto">
          <slot />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import SidebarNav from '../components/SidebarNav.vue';
import TopBar from '../components/TopBar.vue';

import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { clearAuthUser, getAuthUser, peekAuthUser } from '../auth';
import { fetchConfiguracionSistema, fetchConfiguracionUsuario } from '../api/configuracion';
import { applyDensityValue, applyThemeValue, clearStoredUiPrefs } from '../theme';

const user = reactive({
  nombre: '',
  correo: '',
  avatarUrl: null,
  roles: [],
});

const helpUrl = ref('#');
const appName = ref('Bitácora COReFO B.C.');
const appSiglas = ref('COReFO B.C.');
const userReady = ref(false);
const userIcon = ref('account_circle');

const logout = async () => {
  await window.axios.post('/logout');
  clearAuthUser();
  clearStoredUiPrefs();
  window.location.href = '/login';
};

const applyUser = (data) => {
  if (!data) return;
  user.nombre = data.nombre || '';
  user.correo = data.correo || '';
  user.roles = data.roles || [];
};

const loadUser = async () => {
  try {
    const data = await getAuthUser();
    applyUser(data);
  } catch {
    // ignore
  } finally {
    userReady.value = true;
  }
};

const loadConfig = async () => {
  try {
    const items = await fetchConfiguracionSistema();
    const map = Object.fromEntries(items.map((i) => [i.clave, i.valor]));
    helpUrl.value = map['enlaces.ayuda_url'] || '#';
    appName.value = map['app_nombre'] || appName.value;
    appSiglas.value = map['app_siglas'] || appSiglas.value;
  } catch {
    // ignore
  }
};

const applyUserPrefs = (items = []) => {
  const map = Object.fromEntries(items.map((i) => [i.clave, i.valor]));
  if (map['ui.sidebar_icon']) {
    userIcon.value = map['ui.sidebar_icon'];
  }
  if (map['ui.densidad']) {
    applyDensityValue(map['ui.densidad']);
  }
  if (map['tema.modo'] && map['tema.modo'] !== 'global') {
    applyThemeValue(map['tema.modo']);
  }
};

const loadUserPrefs = async () => {
  try {
    const items = await fetchConfiguracionUsuario();
    applyUserPrefs(Array.isArray(items) ? items : []);
  } catch {
    // ignore
  }
};

const handleUserPrefEvent = (event) => {
  const detail = event?.detail || {};
  if (detail.clave === 'ui.sidebar_icon' && detail.valor) {
    userIcon.value = detail.valor;
  }
  if (detail.clave === 'ui.densidad' && detail.valor) {
    applyDensityValue(detail.valor);
  }
  if (detail.clave === 'tema.modo' && detail.valor && detail.valor !== 'global') {
    applyThemeValue(detail.valor);
  }
};

onMounted(() => {
  const cached = peekAuthUser();
  if (cached) {
    applyUser(cached);
    userReady.value = true;
  }
  loadUser();
  loadConfig();
  loadUserPrefs();
  window.addEventListener('bitacora:user-pref', handleUserPrefEvent);
});

onBeforeUnmount(() => {
  window.removeEventListener('bitacora:user-pref', handleUserPrefEvent);
});
</script>
