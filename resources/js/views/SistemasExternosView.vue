<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Sistemas Externos</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Sistemas Externos</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Catálogo de fuentes externas (Eventos RNFO, Incidentes, Licencias) y sus patrones de validación.</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nuevo Sistema</span>
          </button>
        </div>
      </div>

      <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <h4 class="font-bold text-lg">Listado de Sistemas</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Filtros por nombre y estado.</p>
          </div>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
            {{ filteredSistemas.length }} registros
          </span>
        </div>
        <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
          <input
            v-model="query"
            type="text"
            class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
            placeholder="Buscar por nombre, código o patrón..."
          />
          <select v-model="statusFilter" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
          </select>
          <button
            class="px-3 py-1.5 border border-slate-200 dark:border-border-dark rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-border-dark transition-colors"
            @click="clearFilters"
          >
            Limpiar
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
              <tr>
                <th class="px-6 py-4">Nombre</th>
                <th class="px-6 py-4">Patrón</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
              <tr v-for="s in paginatedSistemas" :key="s.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium">{{ s.nombre }}</div>
                  <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ s.codigo }}</div>
                </td>
                <td class="px-6 py-4 text-xs font-mono text-slate-500 dark:text-[#92adc9]">{{ s.patron_regex ?? '-' }}</td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="s.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                  >
                    {{ s.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                  <span v-if="s.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                    En uso
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button class="p-1.5 hover:text-primary transition-colors" @click="openEdit(s)">
                      <span class="material-symbols-outlined text-lg">edit</span>
                    </button>
                    <button
                      class="p-1.5 transition-colors"
                      :class="s.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                      :disabled="s.in_use"
                      :title="s.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar sistema'"
                      @click="deleteSistema(s)"
                    >
                      <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredSistemas.length === 0">
                <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="4">Sin registros</td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationBar
          :page="page"
          :total-pages="totalPages"
          :range-start="range.start"
          :range-end="range.end"
          :total-items="filteredSistemas.length"
          @update:page="page = $event"
        />
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingId ? 'Editar sistema' : 'Nuevo sistema' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: código y nombre.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="form.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="fieldErrors.codigo" class="mt-1 text-xs text-red-500">{{ fieldErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="fieldErrors.nombre" class="mt-1 text-xs text-red-500">{{ fieldErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
              <select v-model="form.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Patrón regex</label>
            <input
              v-model="form.patron_regex"
              type="text"
              class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono"
            />
            <p v-if="fieldErrors.patron_regex" class="mt-1 text-xs text-red-500">{{ fieldErrors.patron_regex }}</p>
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveSistema">
            {{ editingId ? 'Guardar cambios' : 'Crear sistema' }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="toast.visible"
      class="fixed bottom-6 right-6 z-50 rounded-lg px-4 py-3 text-sm font-semibold shadow-lg border"
      :class="toast.type === 'error'
        ? 'bg-red-50 text-red-700 border-red-200'
        : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
    >
      {{ toast.message }}
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../layouts/DashboardLayout.vue';
import PaginationBar from '../components/PaginationBar.vue';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { fetchConfiguracionSistema } from '../api/configuracion';

const sistemas = ref([]);
const query = ref('');
const statusFilter = ref('');
const perPage = ref(20);
const page = ref(1);
const showModal = ref(false);
const editingId = ref(null);
const fieldErrors = reactive({});
const form = reactive({
  codigo: '',
  nombre: '',
  patron_regex: '',
  activo: 1,
});
const toast = reactive({
  visible: false,
  message: '',
  type: 'success',
});

const loadData = async () => {
  const config = await fetchConfiguracionSistema();
  const map = Object.fromEntries(config.map((item) => [item.clave, item.valor]));
  const per = Number(map['paginacion.default_per_page'] ?? 20);
  perPage.value = Number.isFinite(per) && per > 0 ? per : 20;

  const response = await window.axios.get('/api/v1/admin/sistemas-externos');
  sistemas.value = response.data?.data ?? response.data;
};

const filteredSistemas = computed(() => {
  const q = query.value.trim().toLowerCase();
  return sistemas.value.filter((s) => {
    const matchQuery =
      !q ||
      String(s.nombre || '').toLowerCase().includes(q) ||
      String(s.codigo || '').toLowerCase().includes(q) ||
      String(s.patron_regex || '').toLowerCase().includes(q);
    const matchStatus =
      !statusFilter.value ||
      (statusFilter.value === 'activo' && s.activo) ||
      (statusFilter.value === 'inactivo' && !s.activo);
    return matchQuery && matchStatus;
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredSistemas.value.length / perPage.value)));
const paginatedSistemas = computed(() => {
  const start = (page.value - 1) * perPage.value;
  return filteredSistemas.value.slice(start, start + perPage.value);
});
const range = computed(() => {
  if (!filteredSistemas.value.length) return { start: 0, end: 0 };
  const start = (page.value - 1) * perPage.value + 1;
  const end = Math.min(page.value * perPage.value, filteredSistemas.value.length);
  return { start, end };
});

watch([query, statusFilter], () => {
  page.value = 1;
});

watch(totalPages, (value) => {
  if (page.value > value) page.value = value;
});

const clearFilters = () => {
  query.value = '';
  statusFilter.value = '';
};

const resetForm = () => {
  form.codigo = '';
  form.nombre = '';
  form.patron_regex = '';
  form.activo = 1;
  Object.keys(fieldErrors).forEach((key) => {
    fieldErrors[key] = '';
  });
};

const openCreate = () => {
  resetForm();
  editingId.value = null;
  showModal.value = true;
};

const openEdit = (sistema) => {
  resetForm();
  editingId.value = sistema.id;
  form.codigo = sistema.codigo || '';
  form.nombre = sistema.nombre || '';
  form.patron_regex = sistema.patron_regex || '';
  form.activo = sistema.activo ? 1 : 0;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 2600);
};

const saveSistema = async () => {
  Object.keys(fieldErrors).forEach((key) => (fieldErrors[key] = ''));
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingId.value) {
      await window.axios.put(`/api/v1/admin/sistemas-externos/${editingId.value}`, form);
      showToast('Sistema actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/sistemas-externos', form);
      showToast('Sistema creado.');
    }
    await loadData();
    showModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      fieldErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const deleteSistema = async (sistema) => {
  if (sistema.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar el sistema "${sistema.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/sistemas-externos/${sistema.id}`);
    await loadData();
    showToast('Sistema eliminado.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

onMounted(loadData);
</script>
