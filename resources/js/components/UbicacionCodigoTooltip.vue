<template>
  <span
    class="inline-flex max-w-full flex-wrap items-center gap-0.5 font-mono text-xs"
    @mouseenter="openAll($event)"
    @mouseleave="scheduleHide"
  >
    <template v-if="levelItems.length">
      <template v-for="(item, index) in levelItems" :key="`${item.nivel}-${item.codigo}-${index}`">
        <span
          class="cursor-help rounded px-1 py-0.5 transition-colors hover:bg-primary/10 hover:text-primary"
          @mouseenter="openLevel(item, $event)"
        >
          {{ item.codigo }}
        </span>
        <span v-if="index < levelItems.length - 1" class="text-slate-400 dark:text-[#5f7f9f]">-</span>
      </template>
    </template>
    <template v-else>
      <span class="text-slate-500 dark:text-[#92adc9]">Sin código</span>
    </template>
  </span>

  <Teleport to="body">
    <div
      v-if="tooltip.visible"
      ref="tooltipRef"
      class="fixed z-[90] w-[360px] max-w-[calc(100vw-1rem)] rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-[#16222e] p-3 shadow-xl"
      :style="{ left: `${tooltip.left}px`, top: `${tooltip.top}px` }"
      @mouseenter="cancelHide"
      @mouseleave="scheduleHide"
    >
      <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">
        {{ tooltip.mode === 'all' ? 'Ubicación técnica' : `Nivel ${tooltip.level}` }}
      </p>
      <p class="mt-1 text-xs text-slate-700 dark:text-[#d4e4f6] break-all">
        {{ codigo || '-' }}
      </p>

      <div v-if="tooltip.mode === 'all'" class="mt-2 space-y-2 max-h-56 overflow-auto pr-1">
        <div
          v-for="row in levelRows"
          :key="`row-${row.nivel}-${row.codigo}`"
          class="rounded-md border border-slate-100 dark:border-border-dark/60 px-2 py-1.5"
        >
          <p class="text-xs font-semibold text-slate-700 dark:text-[#d4e4f6]">
            {{ `N${row.nivel} · ${row.codigo}` }}
          </p>
          <p class="text-[11px] text-slate-600 dark:text-[#92adc9]">
            {{ row.detalle?.nombre || 'Sin detalle homologado' }}
          </p>
          <p
            v-if="row.detalle?.descripcion"
            class="text-[11px] text-slate-500 dark:text-[#7c9bb9] mt-0.5"
          >
            {{ row.detalle.descripcion }}
          </p>
        </div>
      </div>

      <div v-else-if="activeRow" class="mt-2 space-y-1.5">
        <p class="text-sm font-semibold text-slate-900 dark:text-white">
          {{ `N${activeRow.nivel} · ${activeRow.codigo}` }}
        </p>
        <p class="text-xs text-slate-700 dark:text-[#d4e4f6]">
          {{ activeRow.detalle?.nombre || 'Sin detalle homologado' }}
        </p>
        <p
          v-if="activeRow.detalle?.descripcion"
          class="text-xs text-slate-500 dark:text-[#92adc9]"
        >
          {{ activeRow.detalle.descripcion }}
        </p>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref } from 'vue';

const props = defineProps({
  ubicacion: {
    type: Object,
    default: () => ({}),
  },
  resolveDetalle: {
    type: Function,
    required: true,
  },
});

const tooltipRef = ref(null);
const tooltip = reactive({
  visible: false,
  left: 0,
  top: 0,
  mode: 'all',
  level: null,
});

let hideTimer = null;

const codigo = computed(() => String(props.ubicacion?.codigo || '').trim());

const levelItems = computed(() => {
  const fromFields = [];
  for (let nivel = 1; nivel <= 8; nivel += 1) {
    const value = String(props.ubicacion?.[`nivel_${nivel}`] || '').trim();
    if (!value) continue;
    fromFields.push({ nivel, codigo: value.toUpperCase() });
  }
  if (fromFields.length) return fromFields;

  const segments = codigo.value.split('-').map((item) => item.trim()).filter(Boolean);
  return segments.slice(0, 8).map((segment, index) => ({
    nivel: index + 1,
    codigo: segment.toUpperCase(),
  }));
});

const ramaNivel3 = computed(() => {
  const byField = String(props.ubicacion?.nivel_3 || '').trim().toUpperCase();
  if (byField) return byField;
  const lvl3 = levelItems.value.find((item) => item.nivel === 3);
  return String(lvl3?.codigo || '').trim().toUpperCase();
});

const levelRows = computed(() =>
  levelItems.value.map((item) => ({
    ...item,
    detalle: props.resolveDetalle(item.nivel, item.codigo, ramaNivel3.value),
  }))
);

const activeRow = computed(() => {
  if (tooltip.mode !== 'level' || tooltip.level == null) return null;
  return levelRows.value.find((row) => row.nivel === tooltip.level) || null;
});

const cancelHide = () => {
  if (hideTimer !== null) {
    window.clearTimeout(hideTimer);
    hideTimer = null;
  }
};

const scheduleHide = () => {
  cancelHide();
  hideTimer = window.setTimeout(() => {
    tooltip.visible = false;
    tooltip.mode = 'all';
    tooltip.level = null;
  }, 120);
};

const positionTooltip = (target) => {
  const tooltipEl = tooltipRef.value;
  if (!tooltipEl || !(target instanceof HTMLElement)) return;

  const margin = 8;
  const rect = target.getBoundingClientRect();
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const width = tooltipEl.offsetWidth || 360;
  const height = tooltipEl.offsetHeight || 220;

  let left = rect.left;
  if (left + width + margin > vw) left = vw - width - margin;
  if (left < margin) left = margin;

  let top = rect.bottom + margin;
  if (top + height + margin > vh) {
    top = rect.top - height - margin;
  }
  if (top < margin) top = margin;

  tooltip.left = Math.round(left);
  tooltip.top = Math.round(top);
};

const openAll = (event) => {
  if (!levelItems.value.length) return;
  cancelHide();
  tooltip.visible = true;
  tooltip.mode = 'all';
  tooltip.level = null;

  nextTick(() => {
    positionTooltip(event?.currentTarget);
  });
};

const openLevel = (item, event) => {
  if (!item) return;
  cancelHide();
  tooltip.visible = true;
  tooltip.mode = 'level';
  tooltip.level = item.nivel;

  nextTick(() => {
    positionTooltip(event?.currentTarget);
  });
};

onBeforeUnmount(() => {
  cancelHide();
});
</script>
