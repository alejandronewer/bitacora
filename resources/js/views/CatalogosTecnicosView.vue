<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Catálogos Técnicos</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Catálogos Técnicos</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Criterios temáticos e impactos operativos usados en las entradas de bitácora.</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openCriterioCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nuevo Criterio</span>
          </button>
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openImpactoCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nuevo Impacto</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Criterios Temáticos</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Filtros por nombre/descripcion y estado.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ filteredCriterios.length }} registros
            </span>
          </div>
          <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
            <input
              v-model="criteriosQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar criterio..."
            />
            <select v-model="criteriosStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos</option>
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
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="c in paginatedCriterios" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ c.nombre }}</div>
                    <div v-if="c.descripcion" class="text-xs text-slate-500 dark:text-[#92adc9]">{{ c.descripcion }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="c.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ c.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <span v-if="c.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openCriterioEdit(c)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="c.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="c.in_use"
                        :title="c.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar'"
                        @click="deleteCriterio(c)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredCriterios.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="3">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="criteriosPage"
            :total-pages="criteriosTotalPages"
            :range-start="criteriosRange.start"
            :range-end="criteriosRange.end"
            :total-items="filteredCriterios.length"
            @update:page="criteriosPage = $event"
          />
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Impactos Operativos</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Filtros por nombre/descripcion y estado.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ filteredImpactos.length }} registros
            </span>
          </div>
          <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
            <input
              v-model="impactosQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar impacto..."
            />
            <select v-model="impactosStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos</option>
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
                  <th class="px-6 py-4">Severidad</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="i in paginatedImpactos" :key="i.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ i.nombre }}</div>
                    <div v-if="i.descripcion" class="text-xs text-slate-500 dark:text-[#92adc9]">{{ i.descripcion }}</div>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ i.severidad ?? '-' }}</td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="i.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ i.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <span v-if="i.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        class="p-1.5 transition-colors"
                        :class="isImpactoProtegido(i) ? 'text-slate-300 cursor-not-allowed' : 'hover:text-primary'"
                        :disabled="isImpactoProtegido(i)"
                        title="Editar"
                        @click="openImpactoEdit(i)"
                      >
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="isImpactoProtegido(i) ? 'text-slate-300 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="isImpactoProtegido(i)"
                        title="Eliminar"
                        @click="deleteImpacto(i)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                    <p v-if="isImpactoProtegido(i)" class="mt-1 text-[11px] text-slate-400 dark:text-[#92adc9] text-right">Protegido</p>
                  </td>
                </tr>
                <tr v-if="filteredImpactos.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="4">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="impactosPage"
            :total-pages="impactosTotalPages"
            :range-start="impactosRange.start"
            :range-end="impactosRange.end"
            :total-items="filteredImpactos.length"
            @update:page="impactosPage = $event"
          />
        </div>
      </div>
    </div>

    <div v-if="showCriterioModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingCriterioId ? 'Editar criterio' : 'Nuevo criterio' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: código y nombre.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeCriterioModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="criterioForm.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="criterioErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="criterioErrors.codigo" class="mt-1 text-xs text-red-500">{{ criterioErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="criterioForm.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="criterioErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="criterioErrors.nombre" class="mt-1 text-xs text-red-500">{{ criterioErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Orden</label>
              <input v-model="criterioForm.orden" type="number" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
              <select v-model="criterioForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <input v-model="criterioForm.descripcion" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeCriterioModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveCriterio">
            {{ editingCriterioId ? 'Guardar cambios' : 'Crear criterio' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showImpactoModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingImpactoId ? 'Editar impacto' : 'Nuevo impacto' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: código, nombre y severidad.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeImpactoModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="impactoForm.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="impactoErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="impactoErrors.codigo" class="mt-1 text-xs text-red-500">{{ impactoErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="impactoForm.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="impactoErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="impactoErrors.nombre" class="mt-1 text-xs text-red-500">{{ impactoErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Severidad</label>
              <input
                v-model="impactoForm.severidad"
                type="number"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="impactoErrors.severidad ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="impactoErrors.severidad" class="mt-1 text-xs text-red-500">{{ impactoErrors.severidad }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Orden</label>
              <input v-model="impactoForm.orden" type="number" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
              <select v-model="impactoForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <input v-model="impactoForm.descripcion" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeImpactoModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveImpacto">
            {{ editingImpactoId ? 'Guardar cambios' : 'Crear impacto' }}
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

const criterios = ref([]);
const impactos = ref([]);
const criteriosQuery = ref('');
const criteriosStatus = ref('');
const impactosQuery = ref('');
const impactosStatus = ref('');
const perPage = ref(20);
const criteriosPage = ref(1);
const impactosPage = ref(1);
const showCriterioModal = ref(false);
const showImpactoModal = ref(false);
const editingCriterioId = ref(null);
const editingImpactoId = ref(null);
const criterioErrors = reactive({});
const impactoErrors = reactive({});
const criterioForm = reactive({
  codigo: '',
  nombre: '',
  descripcion: '',
  orden: null,
  activo: 1,
});
const impactoForm = reactive({
  codigo: '',
  nombre: '',
  descripcion: '',
  severidad: null,
  orden: null,
  activo: 1,
});
const toast = reactive({
  visible: false,
  message: '',
  type: 'success',
});

const isImpactoProtegido = (impacto) => {
  const nombre = String(impacto?.nombre || '').toLowerCase();
  return impacto?.codigo === 'ninguno' || impacto?.severidad === 0 || nombre.includes('sin impacto');
};

const loadData = async () => {
  const config = await fetchConfiguracionSistema();
  const map = Object.fromEntries(config.map((item) => [item.clave, item.valor]));
  const per = Number(map['paginacion.default_per_page'] ?? 20);
  perPage.value = Number.isFinite(per) && per > 0 ? per : 20;

  const [criteriosRes, impactosRes] = await Promise.all([
    window.axios.get('/api/v1/admin/catalogos/criterios'),
    window.axios.get('/api/v1/admin/catalogos/impactos'),
  ]);

  criterios.value = criteriosRes.data?.data ?? criteriosRes.data;
  impactos.value = impactosRes.data?.data ?? impactosRes.data;
};

const filteredCriterios = computed(() => {
  const q = criteriosQuery.value.trim().toLowerCase();
  return criterios.value.filter((c) => {
    const matchQuery =
      !q ||
      String(c.nombre || '').toLowerCase().includes(q) ||
      String(c.descripcion || '').toLowerCase().includes(q) ||
      String(c.codigo || '').toLowerCase().includes(q);
    const matchStatus =
      !criteriosStatus.value ||
      (criteriosStatus.value === 'activo' && c.activo) ||
      (criteriosStatus.value === 'inactivo' && !c.activo);
    return matchQuery && matchStatus;
  });
});

const filteredImpactos = computed(() => {
  const q = impactosQuery.value.trim().toLowerCase();
  return impactos.value.filter((i) => {
    const matchQuery =
      !q ||
      String(i.nombre || '').toLowerCase().includes(q) ||
      String(i.descripcion || '').toLowerCase().includes(q) ||
      String(i.codigo || '').toLowerCase().includes(q);
    const matchStatus =
      !impactosStatus.value ||
      (impactosStatus.value === 'activo' && i.activo) ||
      (impactosStatus.value === 'inactivo' && !i.activo);
    return matchQuery && matchStatus;
  });
});

const criteriosTotalPages = computed(() => Math.max(1, Math.ceil(filteredCriterios.value.length / perPage.value)));
const impactosTotalPages = computed(() => Math.max(1, Math.ceil(filteredImpactos.value.length / perPage.value)));

const paginatedCriterios = computed(() => {
  const start = (criteriosPage.value - 1) * perPage.value;
  return filteredCriterios.value.slice(start, start + perPage.value);
});

const paginatedImpactos = computed(() => {
  const start = (impactosPage.value - 1) * perPage.value;
  return filteredImpactos.value.slice(start, start + perPage.value);
});

const criteriosRange = computed(() => {
  if (!filteredCriterios.value.length) return { start: 0, end: 0 };
  const start = (criteriosPage.value - 1) * perPage.value + 1;
  const end = Math.min(criteriosPage.value * perPage.value, filteredCriterios.value.length);
  return { start, end };
});

const impactosRange = computed(() => {
  if (!filteredImpactos.value.length) return { start: 0, end: 0 };
  const start = (impactosPage.value - 1) * perPage.value + 1;
  const end = Math.min(impactosPage.value * perPage.value, filteredImpactos.value.length);
  return { start, end };
});

watch([criteriosQuery, criteriosStatus], () => {
  criteriosPage.value = 1;
});

watch([impactosQuery, impactosStatus], () => {
  impactosPage.value = 1;
});

watch(criteriosTotalPages, (value) => {
  if (criteriosPage.value > value) criteriosPage.value = value;
});

watch(impactosTotalPages, (value) => {
  if (impactosPage.value > value) impactosPage.value = value;
});

const clearFilters = () => {
  criteriosQuery.value = '';
  criteriosStatus.value = '';
  impactosQuery.value = '';
  impactosStatus.value = '';
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 2600);
};

const resetCriterioForm = () => {
  criterioForm.codigo = '';
  criterioForm.nombre = '';
  criterioForm.descripcion = '';
  criterioForm.orden = null;
  criterioForm.activo = 1;
  Object.keys(criterioErrors).forEach((key) => (criterioErrors[key] = ''));
};

const resetImpactoForm = () => {
  impactoForm.codigo = '';
  impactoForm.nombre = '';
  impactoForm.descripcion = '';
  impactoForm.severidad = null;
  impactoForm.orden = null;
  impactoForm.activo = 1;
  Object.keys(impactoErrors).forEach((key) => (impactoErrors[key] = ''));
};

const openCriterioCreate = () => {
  resetCriterioForm();
  editingCriterioId.value = null;
  showCriterioModal.value = true;
};

const openImpactoCreate = () => {
  resetImpactoForm();
  editingImpactoId.value = null;
  showImpactoModal.value = true;
};

const openCriterioEdit = (criterio) => {
  resetCriterioForm();
  editingCriterioId.value = criterio.id;
  criterioForm.codigo = criterio.codigo || '';
  criterioForm.nombre = criterio.nombre || '';
  criterioForm.descripcion = criterio.descripcion || '';
  criterioForm.orden = criterio.orden ?? null;
  criterioForm.activo = criterio.activo ? 1 : 0;
  showCriterioModal.value = true;
};

const openImpactoEdit = (impacto) => {
  if (isImpactoProtegido(impacto)) {
    showToast('Este impacto está protegido.', 'error');
    return;
  }
  resetImpactoForm();
  editingImpactoId.value = impacto.id;
  impactoForm.codigo = impacto.codigo || '';
  impactoForm.nombre = impacto.nombre || '';
  impactoForm.descripcion = impacto.descripcion || '';
  impactoForm.severidad = impacto.severidad ?? null;
  impactoForm.orden = impacto.orden ?? null;
  impactoForm.activo = impacto.activo ? 1 : 0;
  showImpactoModal.value = true;
};

const closeCriterioModal = () => {
  showCriterioModal.value = false;
};

const closeImpactoModal = () => {
  showImpactoModal.value = false;
};

const saveCriterio = async () => {
  Object.keys(criterioErrors).forEach((key) => (criterioErrors[key] = ''));
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingCriterioId.value) {
      await window.axios.put(`/api/v1/admin/catalogos/criterios/${editingCriterioId.value}`, criterioForm);
      showToast('Criterio actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/catalogos/criterios', criterioForm);
      showToast('Criterio creado.');
    }
    await loadData();
    showCriterioModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      criterioErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const saveImpacto = async () => {
  Object.keys(impactoErrors).forEach((key) => (impactoErrors[key] = ''));
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingImpactoId.value) {
      await window.axios.put(`/api/v1/admin/catalogos/impactos/${editingImpactoId.value}`, impactoForm);
      showToast('Impacto actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/catalogos/impactos', impactoForm);
      showToast('Impacto creado.');
    }
    await loadData();
    showImpactoModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      impactoErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const deleteCriterio = async (criterio) => {
  if (criterio.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar el criterio "${criterio.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/catalogos/criterios/${criterio.id}`);
    await loadData();
    showToast('Criterio eliminado.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

const deleteImpacto = async (impacto) => {
  if (isImpactoProtegido(impacto)) {
    showToast('Este impacto está protegido.', 'error');
    return;
  }
  if (impacto.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar el impacto "${impacto.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/catalogos/impactos/${impacto.id}`);
    await loadData();
    showToast('Impacto eliminado.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

onMounted(loadData);
</script>
