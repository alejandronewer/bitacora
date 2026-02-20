<template>
  <header class="h-16 flex items-center justify-between px-8 bg-white dark:bg-surface-dark border-b border-slate-200 dark:border-border-dark shrink-0">
    <div class="flex items-center gap-6 flex-1">
      <h2 class="text-xl font-bold dark:text-white">{{ appName }}</h2>
      <div class="max-w-md w-full relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
        <input class="w-full bg-slate-100 dark:bg-border-dark border-none rounded-lg py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary outline-none" placeholder="Buscar..." type="text" />
      </div>
      <router-link
        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors shadow-sm shadow-primary/20"
        to="/timeline"
      >
        <span class="material-symbols-outlined text-lg">timeline</span>
        <span class="text-sm font-semibold hidden xl:inline">Línea de Tiempo</span>
      </router-link>
    </div>
    <div class="flex items-center gap-4">
      <a :href="helpUrl" class="p-2 text-slate-500 dark:text-[#92adc9] hover:bg-slate-100 dark:hover:bg-border-dark rounded-lg transition-colors">
        <span class="material-symbols-outlined">help</span>
      </a>
      <div class="h-8 w-px bg-slate-200 dark:border-border-dark mx-2"></div>
      <div class="flex items-center gap-2">
        <span v-if="userReady" class="text-sm font-medium hidden lg:inline">Modo {{ modeLabel }}</span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  helpUrl: {
    type: String,
    default: '#',
  },
  appName: {
    type: String,
    default: 'Bitácora COReFO B.C.',
  },
  userReady: {
    type: Boolean,
    default: false,
  },
});

const roleNames = computed(() =>
  (props.user.roles || [])
    .map((role) => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean)
    .map((role) => role.toLowerCase())
);

const modeLabel = computed(() => {
  if (roleNames.value.includes('administrador')) return 'Administrador';
  if (roleNames.value.includes('operador')) return 'Operador';
  return 'Invitado';
});
</script>
