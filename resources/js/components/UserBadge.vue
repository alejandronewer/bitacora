<template>
  <div class="pt-6 border-t border-slate-200 dark:border-border-dark">
    <div class="flex items-center gap-3 p-2">
      <div class="size-10 rounded-full bg-slate-300 flex items-center justify-center" :title="user.nombre">
        <span class="material-symbols-outlined text-slate-600">{{ icon }}</span>
      </div>
      <div class="overflow-hidden">
        <p class="text-sm font-semibold truncate">{{ user.nombre }}</p>
        <p class="text-xs text-slate-500 dark:text-[#92adc9] truncate">{{ user.correo }}</p>
      </div>
    </div>

    <button
      type="button"
      class="mt-3 w-full bg-primary hover:bg-blue-600 text-white font-semibold py-2 rounded-lg transition-all flex items-center justify-center gap-2 shadow-md shadow-primary/10 text-xs"
      @click="onLogout"
    >
      <span class="material-symbols-outlined text-base">logout</span>
      <span>Cerrar sesión</span>
    </button>

  </div>
</template>

<script setup>
const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  icon: {
    type: String,
    default: 'account_circle',
  },
  onLogout: {
    type: Function,
    required: false,
  },
});

const onLogout = () => {
  props.onLogout?.();
};

</script>
