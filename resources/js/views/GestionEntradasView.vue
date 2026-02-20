<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Operación</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Gestión de Entradas</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Gestión de Entradas</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">
            Consulta rápida de entradas, estados y edición directa.
          </p>
        </div>
        <router-link
          class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
          to="/entradas/nueva"
        >
          <span class="material-symbols-outlined">post_add</span>
          <span>Nueva Entrada</span>
        </router-link>
      </div>

      <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="font-bold text-lg">Entradas</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">
              Usa los filtros para encontrar rápido borradores o publicaciones.
            </p>
          </div>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
            {{ totalItems }} registros
          </span>
        </div>

        <div class="px-6 pb-4 flex flex-wrap items-end gap-3">
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Buscar</label>
            <input
              v-model="searchQuery"
              type="text"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[220px]"
              placeholder="Título, texto, resumen..."
              @keyup.enter="applySearch"
            />
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
            <select
              v-model="publicado"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
            >
              <option value="">Todos</option>
              <option value="1">Publicados</option>
              <option value="0">Borradores</option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Tipo</label>
            <select
              v-model="tipoRegistro"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
            >
              <option value="">Todos</option>
              <option value="operativo">Operativo</option>
              <option value="inventario">Inventario</option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Criterio</label>
            <select
              v-model="criterioId"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[200px]"
            >
              <option value="">Todos</option>
              <option v-for="criterio in criteriosActivos" :key="criterio.id" :value="criterio.id">
                {{ criterio.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Impacto</label>
            <select
              v-model="impactoId"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[200px]"
            >
              <option value="">Todos</option>
              <option v-for="impacto in impactosActivos" :key="impacto.id" :value="impacto.id">
                {{ impacto.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de orden</label>
            <select
              v-model="pmOrdenId"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[200px]"
            >
              <option value="">Todas</option>
              <option v-for="orden in pmOrdenesActivas" :key="orden.id" :value="orden.id">
                {{ orden.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de actividad</label>
            <select
              v-model="pmActividadId"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[200px]"
              :disabled="pmOrdenId && pmActividadesFiltradas.length === 0"
            >
              <option value="">Todas</option>
              <option v-for="actividad in pmActividadesFiltradas" :key="actividad.id" :value="actividad.id">
                {{ actividad.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Folio externo</label>
            <input
              v-model="externoId"
              type="text"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[200px]"
              placeholder="Ej. 20240206-0001"
            />
          </div>
          <div v-if="isAdmin" class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Usuario</label>
            <select
              v-model="usuarioId"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm min-w-[200px]"
            >
              <option value="">Todos</option>
              <option v-for="u in usuarios" :key="u.id" :value="u.id">
                {{ u.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Desde</label>
            <input
              v-model="fechaDesde"
              type="date"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
            />
          </div>
          <div class="flex flex-col">
            <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Hasta</label>
            <input
              v-model="fechaHasta"
              type="date"
              class="mt-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
            />
          </div>
          <button
            type="button"
            class="px-3 py-2 border border-slate-200 dark:border-border-dark rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-border-dark transition-colors"
            @click="applyFechaFilter"
          >
            Aplicar fechas
          </button>
          <button
            type="button"
            class="px-3 py-2 border border-slate-200 dark:border-border-dark rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-border-dark transition-colors"
            @click="clearFilters"
          >
            Limpiar
          </button>
        </div>

        <div v-if="fechaError" class="px-6 pb-2 text-xs text-red-500">{{ fechaError }}</div>
        <div v-if="error" class="px-6 pb-4 text-sm text-red-600">{{ error }}</div>

        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
              <tr>
                <th class="px-6 py-4">Fecha</th>
                <th class="px-6 py-4">Título</th>
                <th v-if="isAdmin" class="px-6 py-4">Autor</th>
                <th class="px-6 py-4">Criterio</th>
                <th class="px-6 py-4">Impacto</th>
                <th class="px-6 py-4">Tipo</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
              <tr v-if="loading">
                <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" :colspan="columnCount">
                  Cargando entradas...
                </td>
              </tr>
              <tr v-for="entrada in entradas" :key="entrada.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                <td class="px-6 py-4 text-sm">
                  <div class="font-medium">{{ formatFecha(entrada.fecha_inicio) }}</div>
                  <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ formatHora(entrada.fecha_inicio) }}</div>
                </td>
                <td class="px-6 py-4">
                  <button class="text-sm font-semibold text-slate-900 dark:text-white hover:text-primary transition-colors" @click="openEdit(entrada)">
                    {{ entrada.titulo || 'Sin título' }}
                  </button>
                </td>
                <td v-if="isAdmin" class="px-6 py-4 text-sm">
                  <div class="font-medium">{{ entrada.usuario?.nombre || 'No disponible' }}</div>
                  <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ entrada.usuario?.correo || '' }}</div>
                </td>
                <td class="px-6 py-4 text-sm">{{ entrada.criterio?.nombre || 'No disponible' }}</td>
                <td class="px-6 py-4 text-sm">{{ entrada.impacto?.nombre || 'No disponible' }}</td>
                <td class="px-6 py-4 text-sm capitalize">{{ entrada.tipo_registro || 'No disponible' }}</td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="entrada.publicado ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'"
                  >
                    {{ entrada.publicado ? 'Publicado' : 'Borrador' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button class="p-1.5 hover:text-primary transition-colors" @click="openEdit(entrada)">
                      <span class="material-symbols-outlined text-lg">edit</span>
                    </button>
                    <button class="p-1.5 hover:text-slate-600 transition-colors" @click="openShow(entrada)">
                      <span class="material-symbols-outlined text-lg">visibility</span>
                    </button>
                    <button
                      v-if="isAdmin"
                      class="p-1.5 hover:text-red-600 transition-colors"
                      title="Eliminar entrada"
                      @click="deleteEntrada(entrada)"
                    >
                      <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!loading && entradas.length === 0">
                <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" :colspan="columnCount">
                  Sin registros
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <PaginationBar
          :page="page"
          :total-pages="totalPages"
          :range-start="rangeStart"
          :range-end="rangeEnd"
          :total-items="totalItems"
          @update:page="fetchEntradas"
        />
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import PaginationBar from '../components/PaginationBar.vue';
import { fetchConfiguracionSistema } from '../api/configuracion';
import { getAuthUser } from '../auth';

const router = useRouter();

const entradas = ref([]);
const loading = ref(false);
const error = ref('');
const page = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const rangeStart = ref(0);
const rangeEnd = ref(0);
const perPage = ref(20);

const searchQuery = ref('');
const criterioId = ref('');
const impactoId = ref('');
const pmOrdenId = ref('');
const pmActividadId = ref('');
const externoId = ref('');
const tipoRegistro = ref('');
const publicado = ref('');
const usuarioId = ref('');
const fechaDesde = ref('');
const fechaHasta = ref('');
const fechaError = ref('');

const criterios = ref([]);
const impactos = ref([]);
const pmOrdenes = ref([]);
const pmActividades = ref([]);
const pmMatriz = ref([]);
const usuarios = ref([]);
const authUser = ref(null);

const isAdmin = computed(() => {
  const roles = authUser.value?.roles || [];
  return roles.includes('administrador');
});
const columnCount = computed(() => (isAdmin.value ? 8 : 7));

const criteriosActivos = computed(() => criterios.value.filter((item) => item.activo));
const impactosActivos = computed(() => impactos.value.filter((item) => item.activo));
const pmOrdenesActivas = computed(() => pmOrdenes.value.filter((item) => item.activo));
const pmActividadesActivas = computed(() => pmActividades.value.filter((item) => item.activo));
const pmMatrizActiva = computed(() => pmMatriz.value.filter((item) => item.activo));

const pmActividadesFiltradas = computed(() => {
  if (!pmOrdenId.value) return pmActividadesActivas.value;
  const ordenId = String(pmOrdenId.value);
  const allowedIds = new Set(
    pmMatrizActiva.value
      .filter((row) => String(row.pm_clase_orden_id) === ordenId)
      .map((row) => row.pm_clase_actividad_id)
  );
  return pmActividadesActivas.value.filter((act) => allowedIds.has(act.id));
});

const formatFecha = (value) => {
  if (!value) return 'No disponible';
  const date = new Date(value);
  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium' }).format(date);
};

const formatHora = (value) => {
  if (!value) return '';
  const date = new Date(value);
  return new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit' }).format(date);
};

const loadConfig = async () => {
  try {
    const items = await fetchConfiguracionSistema();
    const map = Object.fromEntries(items.map((item) => [item.clave, item.valor]));
    const per = Number(map['paginacion.default_per_page'] ?? 20);
    perPage.value = Number.isFinite(per) && per > 0 ? per : 20;
  } catch {
    perPage.value = 20;
  }
};

const loadCatalogos = async () => {
  try {
    const [criteriosRes, impactosRes, ordenesRes, actividadesRes, matrizRes] = await Promise.all([
      window.axios.get('/api/v1/catalogos/criterios'),
      window.axios.get('/api/v1/catalogos/impactos'),
      window.axios.get('/api/v1/catalogos/pm/ordenes'),
      window.axios.get('/api/v1/catalogos/pm/actividades'),
      window.axios.get('/api/v1/catalogos/pm/matriz'),
    ]);
    criterios.value = criteriosRes.data?.data ?? criteriosRes.data;
    impactos.value = impactosRes.data?.data ?? impactosRes.data;
    pmOrdenes.value = ordenesRes.data?.data ?? ordenesRes.data;
    pmActividades.value = actividadesRes.data?.data ?? actividadesRes.data;
    pmMatriz.value = matrizRes.data?.data ?? matrizRes.data;
  } catch {
    // ignore
  }
};

const loadAuthUser = async () => {
  try {
    authUser.value = await getAuthUser({ force: true });
  } catch {
    authUser.value = null;
  }
};

const loadUsuarios = async () => {
  if (!isAdmin.value) return;
  try {
    const res = await window.axios.get('/api/v1/admin/usuarios');
    usuarios.value = res.data?.data ?? res.data;
  } catch {
    usuarios.value = [];
  }
};

const buildParams = (targetPage) => {
  const useFecha = Boolean(fechaDesde.value && fechaHasta.value);
  return {
    page: targetPage,
    per_page: perPage.value,
    texto: searchQuery.value || undefined,
    criterio_id: criterioId.value || undefined,
    impacto_id: impactoId.value || undefined,
    pm_clase_orden_id: pmOrdenId.value || undefined,
    pm_clase_actividad_id: pmActividadId.value || undefined,
    externo_id: externoId.value || undefined,
    tipo_registro: tipoRegistro.value || undefined,
    publicado: publicado.value === '' ? undefined : publicado.value,
    usuario_id: isAdmin.value ? (usuarioId.value || undefined) : undefined,
    desde: useFecha ? fechaDesde.value : undefined,
    hasta: useFecha ? fechaHasta.value : undefined,
  };
};

const fetchEntradas = async (targetPage = 1) => {
  loading.value = true;
  error.value = '';
  try {
    const response = await window.axios.get('/api/v1/entradas/gestion', {
      params: buildParams(targetPage),
    });
    const data = response.data?.data ?? [];
    const meta = response.data?.meta ?? {};
    entradas.value = data;
    page.value = meta.current_page ?? targetPage;
    totalPages.value = meta.last_page ?? 1;
    totalItems.value = meta.total ?? data.length;
    rangeStart.value = meta.from ?? (data.length ? 1 : 0);
    rangeEnd.value = meta.to ?? data.length;
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudieron cargar las entradas.';
  } finally {
    loading.value = false;
  }
};

const applySearch = () => {
  fetchEntradas(1);
};

const applyFechaFilter = () => {
  fechaError.value = '';
  if ((fechaDesde.value && !fechaHasta.value) || (!fechaDesde.value && fechaHasta.value)) {
    fechaError.value = 'Selecciona ambas fechas para aplicar el rango.';
    return;
  }
  fetchEntradas(1);
};

const clearFilters = () => {
  searchQuery.value = '';
  criterioId.value = '';
  impactoId.value = '';
  pmOrdenId.value = '';
  pmActividadId.value = '';
  externoId.value = '';
  tipoRegistro.value = '';
  publicado.value = '';
  usuarioId.value = '';
  fechaDesde.value = '';
  fechaHasta.value = '';
  fechaError.value = '';
  fetchEntradas(1);
};

const openEdit = (entrada) => {
  router.push({ name: 'entrada-editar', params: { id: entrada.id } });
};

const openShow = (entrada) => {
  router.push({ name: 'entrada-show', params: { id: entrada.id } });
};

const deleteEntrada = async (entrada) => {
  if (!isAdmin.value) return;

  const nombre = entrada?.titulo || `#${entrada?.id ?? ''}`;
  const confirmed = window.confirm(`Se eliminará la entrada "${nombre}". Esta acción no se puede deshacer. ¿Deseas continuar?`);
  if (!confirmed) return;

  const confirmedAgain = window.confirm('Confirmación final: al eliminar esta entrada se perderán sus datos asociados y no se podrán recuperar. ¿Eliminar definitivamente?');
  if (!confirmedAgain) return;

  try {
    await window.axios.delete(`/api/v1/entradas/${entrada.id}`);
    const nextPage = entradas.value.length === 1 && page.value > 1 ? page.value - 1 : page.value;
    await fetchEntradas(nextPage);
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudo eliminar la entrada.';
  }
};

watch([criterioId, impactoId, pmOrdenId, pmActividadId, externoId, tipoRegistro, publicado, usuarioId], () => {
  fetchEntradas(1);
});

watch(pmOrdenId, () => {
  if (!pmOrdenId.value) {
    pmActividadId.value = '';
    return;
  }
  if (pmActividadId.value && !pmActividadesFiltradas.value.some((act) => String(act.id) === String(pmActividadId.value))) {
    pmActividadId.value = '';
  }
});

onMounted(async () => {
  await loadAuthUser();
  await Promise.all([loadConfig(), loadCatalogos()]);
  await loadUsuarios();
  await fetchEntradas(1);
});
</script>
