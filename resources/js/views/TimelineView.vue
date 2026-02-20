<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark text-slate-900 dark:text-white">
    <header class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-[#233648] bg-background-light dark:bg-background-dark px-4 md:px-10 py-3">
      <div class="max-w-[1440px] mx-auto flex items-center justify-between gap-8">
        <div class="flex items-center gap-8">
          <div class="flex items-center gap-3">
            <div class="size-8 text-primary">
              <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4H17.3334V17.3334H30.6666V30.6666H44V44H4V4Z" fill="currentColor"></path>
              </svg>
            </div>
            <h2 v-if="appNombre" class="text-lg font-bold leading-tight tracking-tight hidden md:block">
              {{ appNombre }}
            </h2>
          </div>
          <div class="hidden lg:flex flex-col min-w-64">
            <div class="flex w-full items-stretch rounded-lg h-10 bg-[#e5e7eb] dark:bg-[#233648]">
              <div class="flex items-center justify-center pl-4 rounded-l-lg">
                <span class="material-symbols-outlined text-[#92adc9]">search</span>
              </div>
              <input
                v-model="searchQuery"
                class="w-full border-none bg-transparent focus:ring-0 text-sm placeholder:text-[#92adc9] px-2"
                placeholder="Buscar registros..."
                @keyup.enter="applySearch"
              />
            </div>
          </div>
        </div>
        <div class="flex items-center gap-4 md:gap-8">
          <router-link
            v-if="!authUser"
            class="flex min-w-[120px] cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold transition-opacity hover:opacity-90"
            to="/login"
          >
            Iniciar Sesión
          </router-link>
          <router-link
            v-else
            class="flex min-w-[120px] cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold transition-opacity hover:opacity-90"
            to="/dashboard"
          >
            Ir al panel
          </router-link>
        </div>
      </div>
    </header>

    <main class="max-w-[1440px] mx-auto p-4 md:p-8 space-y-6">
      <div class="flex items-center gap-2 text-sm">
        <router-link class="text-[#92adc9] hover:text-primary transition-colors" to="/timeline">Inicio</router-link>
        <span class="text-[#92adc9]">/</span>
        <span class="font-medium">Línea de Tiempo Pública</span>
      </div>

      <div class="flex flex-col gap-2">
        <h1 class="text-4xl font-black tracking-tight leading-tight">Bitácora de Operaciones</h1>
        <p class="text-[#92adc9] text-base">Cronología de eventos técnicos publicados y novedades de red.</p>
      </div>

      <div class="flex flex-col lg:flex-row gap-6">
        <aside class="w-full lg:w-72 shrink-0">
          <div class="sticky top-24 flex flex-col gap-6 bg-white dark:bg-[#16222e] rounded-xl p-6 border border-slate-200 dark:border-[#233648]">
          <div>
            <h1 class="text-base font-bold mb-1">Filtros de búsqueda</h1>
          </div>
          <div class="space-y-4">
            <hr class="border-slate-200 dark:border-[#233648]" />
            <div class="space-y-2">
              <p class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">Catálogos</p>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Criterio temático</label>
                <select
                  v-model="criterioId"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                >
                  <option value="">Todos</option>
                  <option v-for="criterio in criteriosActivos" :key="criterio.id" :value="criterio.id">
                    {{ criterio.nombre }}
                  </option>
                </select>
              </div>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nivel de impacto</label>
                <select
                  v-model="impactoId"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                >
                  <option value="">Todos</option>
                  <option v-for="impacto in impactosActivos" :key="impacto.id" :value="impacto.id">
                    {{ impacto.nombre }}
                  </option>
                </select>
              </div>
            </div>
            <hr class="border-slate-200 dark:border-[#233648]" />
            <div class="space-y-2">
              <p class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">Tipo de registro</p>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Operativo / Inventario</label>
                <select
                  v-model="tipoRegistro"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                >
                  <option value="">Todos</option>
                  <option value="operativo">Operativo</option>
                  <option value="inventario">Inventario</option>
                </select>
              </div>
            </div>
            <hr class="border-slate-200 dark:border-[#233648]" />
            <div class="space-y-2">
              <p class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">PM-Like</p>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de orden</label>
                <select
                  v-model="pmOrdenId"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                >
                  <option value="">Todas</option>
                  <option v-for="orden in pmOrdenesActivas" :key="orden.id" :value="orden.id">
                    {{ orden.nombre }}
                  </option>
                </select>
              </div>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de actividad</label>
                <select
                  v-model="pmActividadId"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                  :disabled="pmOrdenId && pmActividadesFiltradas.length === 0"
                >
                  <option value="">Todas</option>
                  <option v-for="actividad in pmActividadesFiltradas" :key="actividad.id" :value="actividad.id">
                    {{ actividad.nombre }}
                  </option>
                </select>
              </div>
            </div>
            <hr class="border-slate-200 dark:border-[#233648]" />
            <div class="space-y-2">
              <p class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">Referencias externas</p>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Folio</label>
                <input
                  v-model="externoId"
                  type="text"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                  placeholder="Ej. 20240206-0001"
                />
              </div>
            </div>
            <button
              type="button"
              class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors"
              @click="clearCatalogoFilters"
            >
              <span class="material-symbols-outlined text-base">restart_alt</span>
              Limpiar filtros de búsqueda
            </button>
          </div>
          <hr class="border-slate-200 dark:border-[#233648]" />
          <div class="flex flex-col gap-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#92adc9]">Filtros rápidos</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="filtro in filtros"
                :key="filtro.key"
                type="button"
                class="flex h-7 items-center justify-center rounded-full px-3 border text-[11px] font-semibold transition-colors"
                :class="chipClass(filtro.key)"
                @click="setFiltro(filtro.key)"
              >
                {{ filtro.label }}
              </button>
            </div>
          </div>
        </div>
        </aside>

        <section class="flex-1 flex flex-col gap-6">
          <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
          </div>

          <div class="flex flex-col gap-5">
            <div v-if="loading && entradasFiltradas.length === 0" class="text-sm text-slate-500 dark:text-[#92adc9]">
              Cargando registros publicados...
            </div>

            <article
              v-for="entrada in entradasFiltradas"
              :key="entrada.id"
              class="bg-white dark:bg-[#16222e] rounded-xl border border-slate-200 dark:border-[#233648] p-6 hover:shadow-xl transition-shadow flex flex-col md:flex-row gap-6"
            >
            <div class="flex flex-col items-center justify-start shrink-0">
              <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase">{{ entrada.fecha_label }}</span>
              <span class="text-2xl font-black">{{ entrada.fecha_dia }}</span>
              <span class="text-xs font-medium text-slate-400 dark:text-slate-500 uppercase">{{ entrada.fecha_mes }}</span>
            </div>
            <div class="flex-1 flex flex-col gap-3">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold border" :class="entrada.badge_class">
                    {{ entrada.badge_label }}
                  </span>
                  <span
                    v-if="authUser"
                    class="px-2 py-0.5 rounded bg-green-500/10 text-green-500 text-[10px] font-bold border border-green-500/20 uppercase tracking-wide"
                  >
                    Publicado
                  </span>
                </div>
                <span class="text-xs text-[#92adc9] font-medium">Iniciado: {{ entrada.hora_inicio || 'No disponible' }}</span>
              </div>
              <h3 class="text-xl font-bold hover:text-primary cursor-pointer transition-colors" @click="openEntrada(entrada)">
                {{ entrada.titulo || 'Entrada sin título' }}
              </h3>
              <p v-if="entrada.resumen_tecnico" class="text-xs text-slate-500 dark:text-[#92adc9] leading-relaxed">
                {{ entrada.resumen_tecnico }}
              </p>
              <p class="text-slate-600 dark:text-[#92adc9] text-sm leading-relaxed">
                {{ entrada.preview }}
              </p>
              <div class="flex flex-wrap items-center gap-4 mt-2">
                <div v-if="entrada.criterio" class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                  <span class="material-symbols-outlined text-base">sell</span>
                  <span>{{ entrada.criterio }}</span>
                </div>
                <div v-if="entrada.impacto" class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                  <span class="material-symbols-outlined text-base">warning</span>
                  <span>{{ entrada.impacto }}</span>
                </div>
              </div>
            </div>
            </article>

            <div v-if="!loading && entradasFiltradas.length === 0" class="text-sm text-slate-500 dark:text-[#92adc9]">
              No hay entradas publicadas para los filtros actuales.
            </div>

            <div class="flex justify-center mt-6">
              <button
                class="flex items-center gap-2 px-6 py-2 rounded-lg bg-slate-100 dark:bg-[#233648] font-medium text-sm hover:bg-slate-200 dark:hover:bg-[#2d445a] transition-colors disabled:opacity-60"
                :disabled="!hasMore || loading"
                @click="loadMore"
              >
                {{ hasMore ? 'Cargar más registros' : 'No hay más resultados' }}
                <span class="material-symbols-outlined text-base">expand_more</span>
              </button>
            </div>
          </div>
        </section>

        <aside class="w-full lg:w-72 shrink-0">
          <div class="sticky top-24 flex flex-col gap-4 bg-white dark:bg-[#16222e] rounded-xl p-6 border border-slate-200 dark:border-[#233648]">
            <div>
              <h2 class="text-base font-bold mb-1">Rango de fechas</h2>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Filtra por fecha de inicio</p>
            </div>
            <div class="space-y-3">
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Desde</label>
                <input
                  v-model="fechaDesde"
                  type="date"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                />
              </div>
              <div>
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Hasta</label>
                <input
                  v-model="fechaHasta"
                  type="date"
                  class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                />
              </div>
            </div>
            <div v-if="fechaError" class="text-[11px] text-red-500">{{ fechaError }}</div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg bg-primary text-white px-3 py-2 text-xs font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors"
                @click="applyFechaFilter"
              >
                Aplicar
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 text-xs font-semibold text-slate-600 dark:text-[#92adc9] hover:border-primary hover:text-primary transition-colors"
                @click="clearFechaFilter"
              >
                Limpiar
              </button>
            </div>
            <hr class="border-slate-200 dark:border-[#233648]" />
            <div class="space-y-3">
              <div>
                <h2 class="text-base font-bold mb-1">Equipos asociados</h2>
                <p class="text-xs text-slate-500 dark:text-[#92adc9]">Filtra por elemento NMS/EMS o por equipo SAP-like asociado.</p>
              </div>

              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Inventario NMS/EMS</label>
                <div class="flex items-center gap-2">
                  <input
                    v-model="inventarioBusqueda"
                    type="text"
                    class="flex-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                    placeholder="Código o nombre"
                    @keyup.enter="buscarInventarioOpciones"
                  />
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-border-dark px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-[#92adc9] hover:border-primary hover:text-primary transition-colors"
                    :disabled="inventarioCargando"
                    @click="buscarInventarioOpciones"
                  >
                    {{ inventarioCargando ? '...' : 'Buscar' }}
                  </button>
                </div>
                <select
                  v-model="inventarioElementoId"
                  class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                >
                  <option value="">Todos</option>
                  <option v-for="item in inventarioOpciones" :key="item.id" :value="item.id">
                    {{ item.codigo || `#${item.id}` }} · {{ item.nombre || 'Sin nombre' }} · {{ formatTipoInventario(item.tipo) }}
                  </option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Equipo SAP-like</label>
                <div class="flex items-center gap-2">
                  <input
                    v-model="equipoBusqueda"
                    type="text"
                    class="flex-1 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                    placeholder="Código o nombre"
                    @keyup.enter="buscarEquiposOpciones"
                  />
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-border-dark px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-[#92adc9] hover:border-primary hover:text-primary transition-colors"
                    :disabled="equiposCargando"
                    @click="buscarEquiposOpciones"
                  >
                    {{ equiposCargando ? '...' : 'Buscar' }}
                  </button>
                </div>
                <select
                  v-model="equipoId"
                  class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary outline-none"
                >
                  <option value="">Todos</option>
                  <option v-for="item in equipoOpciones" :key="item.id" :value="item.id">
                    {{ item.codigo || `#${item.id}` }} · {{ item.nombre || 'Sin nombre' }}
                  </option>
                </select>
              </div>
              <button
                type="button"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors"
                @click="clearAsociacionFilters"
              >
                <span class="material-symbols-outlined text-base">restart_alt</span>
                Limpiar filtros de asociación
              </button>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <footer class="max-w-[1440px] mx-auto p-8 border-t border-slate-200 dark:border-[#233648] mt-12">
      <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-xs text-slate-500 dark:text-[#92adc9]">
          © 2026 Mantenimiento y Operación de Redes de Comunicaciones - Ing. Jesús Alejandro Pelayo Manríquez
        </p>
        <div class="flex items-center gap-6 text-[11px] text-slate-500 dark:text-[#92adc9] font-medium uppercase tracking-widest">
          <a class="hover:text-slate-900 dark:hover:text-white transition-colors" :href="links.gtrbc">G.R.T.B.C.</a>
          <span class="text-slate-300 dark:text-[#233648]">|</span>
          <a class="hover:text-slate-900 dark:hover:text-white transition-colors" :href="links.subger">Subger. de Comunicaciones</a>
          <span class="text-slate-300 dark:text-[#233648]">|</span>
          <span>v1.0.0</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { fetchConfiguracionSistema } from '../api/configuracion';

const entradas = ref([]);
const loading = ref(false);
const error = ref('');
const page = ref(1);
const hasMore = ref(true);
const searchQuery = ref('');
const filtroActivo = ref('todo');
const criterios = ref([]);
const impactos = ref([]);
const criterioId = ref('');
const impactoId = ref('');
const pmOrdenes = ref([]);
const pmActividades = ref([]);
const pmMatriz = ref([]);
const pmOrdenId = ref('');
const pmActividadId = ref('');
const externoId = ref('');
const tipoRegistro = ref('');
const inventarioElementoId = ref('');
const inventarioBusqueda = ref('');
const inventarioOpciones = ref([]);
const inventarioCargando = ref(false);
const equipoId = ref('');
const equipoBusqueda = ref('');
const equipoOpciones = ref([]);
const equiposCargando = ref(false);
const fechaDesde = ref('');
const fechaHasta = ref('');
const fechaError = ref('');
const pendingReload = ref(false);
const filtros = [
  { key: 'todo', label: 'Todo', icon: 'layers' },
  { key: 'fallas', label: 'Fallas', icon: 'report' },
  { key: 'anomalias', label: 'Anomalías', icon: 'warning' },
];
const appNombre = ref('');
const authUser = ref(null);
const router = useRouter();
const links = ref({
  gtrbc: '#',
  subger: '#',
});

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

const formatMes = (fecha) =>
  new Intl.DateTimeFormat('es-MX', { month: 'short' }).format(fecha).replace('.', '').toUpperCase();

const formatHora = (fecha) =>
  new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit' }).format(fecha);

const formatTipoInventario = (tipo) => {
  const map = {
    nodo: 'Nodo',
    enlace: 'Enlace',
    servicio: 'Servicio',
    tunel: 'Túnel',
    trail: 'Trail',
  };
  return map[String(tipo || '').toLowerCase()] || 'Sin tipo';
};

const diffLabel = (fecha) => {
  const now = new Date();
  const start = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const diff = (today - start) / (1000 * 60 * 60 * 24);
  if (diff === 0) return 'Hoy';
  if (diff === 1) return 'Ayer';
  return formatMes(fecha);
};

const buildEntradaCard = (entrada) => {
  const fecha = entrada.fecha_inicio ? new Date(entrada.fecha_inicio) : new Date();
  const textoBase = entrada.cuerpo_texto || entrada.resumen_tecnico || '';
  const preview = textoBase.length > 200 ? `${textoBase.slice(0, 200)}...` : textoBase || 'Sin descripción registrada.';

  const impacto = entrada.impacto?.nombre || '';
  const criterio = entrada.criterio?.nombre || '';
  const badgeLabel = entrada.tipo_registro === 'inventario' ? 'INVENTARIO' : 'OPERATIVO';
  const badgeClass = entrada.tipo_registro === 'inventario'
    ? 'bg-blue-500/10 text-blue-500 border-blue-500/20'
    : 'bg-amber-500/10 text-amber-500 border-amber-500/20';

  return {
    ...entrada,
    fecha_label: diffLabel(fecha),
    fecha_dia: fecha.getDate(),
    fecha_mes: formatMes(fecha),
    hora_inicio: entrada.fecha_inicio ? formatHora(fecha) : null,
    preview,
    criterio,
    impacto,
    badge_label: badgeLabel,
    badge_class: badgeClass,
  };
};

const entradasDecoradas = computed(() => entradas.value.map(buildEntradaCard));
const canEditar = computed(() => {
  const roles = authUser.value?.roles || [];
  return roles.includes('administrador') || roles.includes('operador');
});

const entradasFiltradas = computed(() => {
  if (filtroActivo.value === 'todo') return entradasDecoradas.value;
  const keywordMap = {
    fallas: 'falla',
    anomalias: 'anomal',
  };
  const keyword = keywordMap[filtroActivo.value];
  if (!keyword) return entradasDecoradas.value;

  return entradasDecoradas.value.filter((entrada) => {
    const texto = `${entrada.titulo || ''} ${entrada.resumen_tecnico || ''} ${entrada.cuerpo_texto || ''} ${entrada.criterio || ''}`.toLowerCase();
    return texto.includes(keyword);
  });
});

const fetchEntradas = async ({ reset = false } = {}) => {
  if (loading.value) {
    if (reset) pendingReload.value = true;
    return;
  }
  loading.value = true;
  error.value = '';

  if (reset) {
    page.value = 1;
    entradas.value = [];
    hasMore.value = true;
  }

  try {
    const useFecha = Boolean(fechaDesde.value && fechaHasta.value);
    const response = await window.axios.get('/api/v1/entradas', {
      params: {
        publicado: 1,
        page: page.value,
        texto: searchQuery.value || undefined,
        criterio_id: criterioId.value || undefined,
        impacto_id: impactoId.value || undefined,
        pm_clase_orden_id: pmOrdenId.value || undefined,
        pm_clase_actividad_id: pmActividadId.value || undefined,
        externo_id: externoId.value || undefined,
        tipo_registro: tipoRegistro.value || undefined,
        inventario_elemento_id: inventarioElementoId.value || undefined,
        equipo_id: equipoId.value || undefined,
        desde: useFecha ? fechaDesde.value : undefined,
        hasta: useFecha ? fechaHasta.value : undefined,
      },
    });

    const data = response.data?.data ?? [];
    const meta = response.data?.meta;
    entradas.value = reset ? data : [...entradas.value, ...data];

    if (meta?.current_page && meta?.last_page) {
      hasMore.value = meta.current_page < meta.last_page;
      page.value = meta.current_page + 1;
    } else {
      hasMore.value = data.length >= 20;
      page.value += 1;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudo cargar la línea de tiempo.';
  } finally {
    loading.value = false;
    if (pendingReload.value) {
      pendingReload.value = false;
      fetchEntradas({ reset: true });
    }
  }
};

const loadMore = () => {
  if (!hasMore.value) return;
  fetchEntradas();
};

const applySearch = () => {
  fetchEntradas({ reset: true });
};

const setFiltro = (key) => {
  filtroActivo.value = key;
};

const clearCatalogoFilters = () => {
  criterioId.value = '';
  impactoId.value = '';
  pmOrdenId.value = '';
  pmActividadId.value = '';
  externoId.value = '';
  tipoRegistro.value = '';
};

const applyFechaFilter = () => {
  fechaError.value = '';
  if ((fechaDesde.value && !fechaHasta.value) || (!fechaDesde.value && fechaHasta.value)) {
    fechaError.value = 'Selecciona ambas fechas para aplicar el filtro.';
    return;
  }
  fetchEntradas({ reset: true });
};

const clearFechaFilter = () => {
  fechaDesde.value = '';
  fechaHasta.value = '';
  fechaError.value = '';
  fetchEntradas({ reset: true });
};

const clearAsociacionFilters = () => {
  inventarioElementoId.value = '';
  inventarioBusqueda.value = '';
  inventarioOpciones.value = [];
  equipoId.value = '';
  equipoBusqueda.value = '';
  equipoOpciones.value = [];
};

const buscarInventarioOpciones = async () => {
  const q = inventarioBusqueda.value.trim();
  if (q.length < 2) {
    inventarioOpciones.value = [];
    return;
  }

  inventarioCargando.value = true;
  try {
    const response = await window.axios.get('/api/v1/inventario/elementos', {
      params: {
        q,
        limit: 120,
      },
    });
    inventarioOpciones.value = response.data?.data ?? response.data ?? [];
  } catch {
    inventarioOpciones.value = [];
  } finally {
    inventarioCargando.value = false;
  }
};

const buscarEquiposOpciones = async () => {
  const q = equipoBusqueda.value.trim();
  if (q.length < 2) {
    equipoOpciones.value = [];
    return;
  }

  equiposCargando.value = true;
  try {
    const response = await window.axios.get('/api/v1/equipos', {
      params: {
        q,
        limit: 120,
      },
    });
    equipoOpciones.value = response.data?.data ?? response.data ?? [];
  } catch {
    equipoOpciones.value = [];
  } finally {
    equiposCargando.value = false;
  }
};

const openEntrada = (entrada) => {
  router.push({ name: 'entrada-show', params: { id: entrada.id } });
};

const chipClass = (key) => {
  const isActive = filtroActivo.value === key;
  return isActive
    ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20'
    : 'bg-white dark:bg-[#233648] border-slate-200 dark:border-transparent text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-[#2d445a]';
};

const loadConfig = async () => {
  try {
    const items = await fetchConfiguracionSistema();
    const map = Object.fromEntries(items.map((item) => [item.clave, item.valor]));
    if (map.app_nombre) {
      appNombre.value = map.app_nombre;
    }
    links.value = {
      gtrbc: map['enlaces.gtrbc_url'] || '#',
      subger: map['enlaces.subger_comunicaciones_url'] || '#',
    };
  } catch {
    // ignore
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
    const response = await window.axios.get('/api/v1/me', { skipAuthRedirect: true });
    authUser.value = response.data?.data ?? response.data;
  } catch {
    authUser.value = null;
  }
};

onMounted(async () => {
  await loadAuthUser();
  await loadConfig();
  await loadCatalogos();
  await fetchEntradas({ reset: true });
});

watch([criterioId, impactoId, pmOrdenId, pmActividadId, externoId, tipoRegistro, inventarioElementoId, equipoId], () => {
  fetchEntradas({ reset: true });
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
</script>
