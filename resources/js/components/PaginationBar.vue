<template>
  <div
    v-if="totalItems > 0"
    class="p-4 border-t border-slate-200 dark:border-border-dark flex items-center justify-between"
  >
    <p class="text-sm text-slate-500 dark:text-[#92adc9]">
      Mostrando {{ rangeStart }}-{{ rangeEnd }} de {{ totalItems }}
    </p>
    <div class="flex items-center gap-2">
      <button
        class="px-3 py-1 border border-slate-200 dark:border-border-dark rounded text-sm hover:bg-slate-50 dark:hover:bg-border-dark disabled:opacity-50"
        :disabled="page <= 1"
        @click="emitPage(page - 1)"
      >
        Anterior
      </button>
      <button class="px-3 py-1 bg-primary text-white rounded text-sm font-medium">{{ page }}</button>
      <button
        class="px-3 py-1 border border-slate-200 dark:border-border-dark rounded text-sm hover:bg-slate-50 dark:hover:bg-border-dark disabled:opacity-50"
        :disabled="page >= totalPages"
        @click="emitPage(page + 1)"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  page: {
    type: Number,
    required: true,
  },
  totalPages: {
    type: Number,
    required: true,
  },
  rangeStart: {
    type: Number,
    required: true,
  },
  rangeEnd: {
    type: Number,
    required: true,
  },
  totalItems: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['update:page']);

const emitPage = (next) => {
  if (next === props.page) return;
  emit('update:page', next);
};
</script>
