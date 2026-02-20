<template>
  <main class="min-h-screen bg-background-light dark:bg-background-dark text-slate-900 dark:text-white industrial-bg flex flex-col">
    <div class="flex-1 flex items-center justify-center p-4">
      <div class="w-full max-w-[440px]">
        <div class="flex flex-col items-center mb-10">
          <div class="size-16 text-primary mb-4">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M4 4H17.3334V17.3334H30.6666V30.6666H44V44H4V4Z" fill="currentColor" />
            </svg>
          </div>
          <h1 class="text-2xl font-black tracking-tight uppercase">Bitácora COReFO B.C.</h1>
          <p class="text-slate-500 dark:text-[#92adc9] text-sm mt-2">Plataforma de Control y Registro Operativo</p>
        </div>

        <div class="bg-white dark:bg-[#16222e] border border-slate-200 dark:border-[#233648] rounded-xl p-8 shadow-2xl">
          <h2 class="text-xl font-bold mb-6">Acceso al Sistema</h2>

          <form class="space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]" for="correo">
                Correo electrónico
              </label>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-[#92adc9] text-lg">
                  mail
                </span>
                <input
                  id="correo"
                  v-model.trim="form.correo"
                  name="correo"
                  type="email"
                  required
                  autocomplete="username"
                  placeholder="usuario@telecom.com"
                  class="w-full bg-slate-50 dark:bg-[#101922] border border-slate-200 dark:border-[#233648] rounded-lg py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                />
              </div>
              <p v-if="fieldErrors.correo" class="text-red-500 text-[11px] font-medium">
                {{ fieldErrors.correo }}
              </p>
            </div>

            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]" for="password">
                  Contraseña
                </label>
              </div>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-[#92adc9] text-lg">
                  lock
                </span>
                <input
                  id="password"
                  v-model="form.password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="••••••••"
                  class="w-full bg-slate-50 dark:bg-[#101922] border border-slate-200 dark:border-[#233648] rounded-lg py-2.5 pl-10 pr-10 text-sm text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600"
                />
                <button
                  type="button"
                  class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-[#92adc9] text-lg hover:text-slate-900 dark:hover:text-white transition-colors"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? 'visibility_off' : 'visibility' }}
                </button>
              </div>
              <p v-if="fieldErrors.password" class="text-red-500 text-[11px] font-medium">
                {{ fieldErrors.password }}
              </p>
              <p v-if="error" class="text-red-500 text-[11px] font-medium flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined text-xs">error</span>
                {{ error }}
              </p>
            </div>

            <button
              class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition-all flex items-center justify-center gap-3 shadow-lg shadow-primary/10 disabled:opacity-70"
              type="submit"
              :disabled="loading"
            >
              <span v-if="loading" class="material-symbols-outlined text-xl loader-spin">progress_activity</span>
              <span>{{ loading ? 'Ingresando...' : 'Iniciar Sesión' }}</span>
            </button>
          </form>
        </div>

        <div class="mt-8 flex justify-center gap-6 text-[11px] text-slate-500 dark:text-[#92adc9] font-medium uppercase tracking-widest">
          <a class="hover:text-slate-900 dark:hover:text-white transition-colors" :href="links.gtrbc">G.R.T.B.C.</a>
          <span class="text-slate-300 dark:text-[#233648]">|</span>
          <a class="hover:text-slate-900 dark:hover:text-white transition-colors" :href="links.subger">Subger. de Comunicaciones</a>
          <span class="text-slate-300 dark:text-[#233648]">|</span>
          <a>v1.0.0</a>
        </div>
      </div>
    </div>

    <footer class="w-full max-w-[1440px] mx-auto p-8 opacity-50">
      <div class="flex flex-col md:flex-row justify-center items-center gap-4">
        <p class="text-xs text-slate-500 dark:text-[#92adc9]">© 2026 Mantenimiento y Operación de Redes de Comunicaciones - Ing. Jesús Alejandro Pelayo Manríquez</p>
      </div>
    </footer>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { fetchConfiguracionSistema } from '../api/configuracion';

const router = useRouter();

const loading = ref(false);
const error = ref('');
const showPassword = ref(false);
const fieldErrors = reactive({ correo: '', password: '' });
const links = reactive({
  gtrbc: '#',
  subger: '#',
});

const form = reactive({
  correo: '',
  password: '',
});

const resetErrors = () => {
  error.value = '';
  fieldErrors.correo = '';
  fieldErrors.password = '';
};

const submit = async () => {
  resetErrors();
  loading.value = true;

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    await window.axios.post('/login', {
      correo: form.correo,
      password: form.password,
    });

    router.push('/dashboard');
  } catch (e) {
    if (e.response?.status === 422 && e.response?.data?.errors) {
      const errors = e.response.data.errors;
      fieldErrors.correo = errors.correo?.[0] || '';
      fieldErrors.password = errors.password?.[0] || '';
    }

    error.value = e.response?.data?.message || 'Credenciales incorrectas. Intente nuevamente.';
  } finally {
    loading.value = false;
  }
};

const loadConfig = async () => {
  try {
    const items = await fetchConfiguracionSistema();
    const map = Object.fromEntries(items.map((i) => [i.clave, i.valor]));
    links.gtrbc = map['enlaces.gtrbc_url'] || '#';
    links.subger = map['enlaces.subger_comunicaciones_url'] || '#';
  } catch {
    // keep defaults
  }
};

loadConfig();
</script>
