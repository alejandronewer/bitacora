<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Dashboard</h1>
        <p class="text-slate-500 dark:text-[#92adc9]">Resumen operativo de la bitácora.</p>
      </div>

      <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </div>

      <template v-for="(section, index) in orderedSections" :key="section">
        <hr v-if="index > 0" class="border-slate-200/70 dark:border-border-dark" />

        <template v-if="section === 'resumen'">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-slate-200/70 dark:border-border-dark bg-gradient-to-br from-white to-slate-50 dark:from-surface-dark dark:to-[#141f28] p-6 shadow-sm">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="inline-flex size-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined text-xl">calendar_month</span>
                  </span>
                  <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Publicadas</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-white">Mes actual</p>
                  </div>
                </div>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ mesLabel }}</span>
              </div>
              <div class="mt-5 flex items-end justify-between">
                <span class="text-4xl font-black text-slate-900 dark:text-white">{{ stats.publicadas_mes }}</span>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">Entradas</span>
              </div>
              <div class="mt-3 h-1.5 w-full rounded-full bg-slate-100 dark:bg-border-dark/60 overflow-hidden">
                <div class="h-full w-2/3 bg-blue-500/60 rounded-full"></div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-border-dark bg-gradient-to-br from-white to-slate-50 dark:from-surface-dark dark:to-[#141f28] p-6 shadow-sm">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="inline-flex size-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-xl">event_available</span>
                  </span>
                  <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Publicadas</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-white">Año actual</p>
                  </div>
                </div>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ anioLabel }}</span>
              </div>
              <div class="mt-5 flex items-end justify-between">
                <span class="text-4xl font-black text-slate-900 dark:text-white">{{ stats.publicadas_anio }}</span>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">Entradas</span>
              </div>
              <div class="mt-3 h-1.5 w-full rounded-full bg-slate-100 dark:bg-border-dark/60 overflow-hidden">
                <div class="h-full w-4/5 bg-emerald-500/60 rounded-full"></div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-border-dark bg-gradient-to-br from-white to-slate-50 dark:from-surface-dark dark:to-[#141f28] p-6 shadow-sm">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="inline-flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined text-xl">stacked_bar_chart</span>
                  </span>
                  <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Entradas</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-white">Total en sistema</p>
                  </div>
                </div>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">Sistema</span>
              </div>
              <div class="mt-5 flex items-end justify-between">
                <span class="text-4xl font-black text-slate-900 dark:text-white">{{ stats.total }}</span>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">Registros</span>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-[#92adc9]">
                <span class="inline-flex items-center gap-1 rounded-full bg-green-500/10 text-green-600 dark:text-green-400 px-2 py-0.5 text-[11px] font-semibold">
                  Publicadas: {{ stats.publicadas }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 text-[11px] font-semibold">
                  Borradores: {{ stats.borradores }}
                </span>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="section === 'mensuales'">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Widgets mensuales</p>
              <h2 class="text-lg font-bold text-slate-900 dark:text-white">Impacto, eventos y ubicaciones</h2>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Periodo vigente: {{ widgetsPeriodoLabel }}</p>
            </div>
            <div class="rounded-xl border border-slate-200/70 dark:border-border-dark bg-slate-50/80 dark:bg-border-dark/40 px-3 py-2">
              <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Periodo de widgets</p>
              <div class="mt-2 flex flex-wrap items-end gap-2 text-xs text-slate-500 dark:text-[#92adc9]">
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Mes</label>
                  <select
                    v-model.number="widgetsMesDraft"
                    class="mt-1 w-32 bg-white dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  >
                    <option v-for="m in 12" :key="m" :value="m">{{ new Date(2025, m - 1, 1).toLocaleString('es-MX', { month: 'long' }) }}</option>
                  </select>
                </div>
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Año</label>
                  <input
                    v-model.number.lazy="widgetsAnioDraft"
                    type="number"
                    min="2000"
                    max="2100"
                    class="mt-1 w-24 bg-white dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  />
                </div>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-lg bg-primary text-white px-3 py-2 text-xs font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors"
                  @click="applyWidgetsPeriodo"
                >
                  Aplicar fecha
                </button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-slate-200/70 dark:border-border-dark bg-white dark:bg-surface-dark p-6 shadow-sm">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Impacto</p>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Severidad · mes seleccionado</h3>
                </div>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ widgetsPeriodoLabel }}</span>
              </div>
              <div class="mt-4 h-[220px]">
                <canvas ref="impactoChartCanvas"></canvas>
              </div>
              <p v-if="!impactoSeries.data.length" class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                Sin datos registrados este mes.
              </p>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-border-dark bg-white dark:bg-surface-dark p-6 shadow-sm">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Eventos detectados</p>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Falla vs Anomalía</h3>
                </div>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ widgetsPeriodoLabel }}</span>
              </div>
              <div class="mt-4 flex flex-col sm:flex-row items-center gap-4">
                <div class="h-40 w-40">
                  <canvas ref="eventosChartCanvas"></canvas>
                </div>
                <div class="space-y-2 text-xs text-slate-500 dark:text-[#92adc9]">
                  <div class="flex items-center justify-between gap-4">
                    <span class="inline-flex items-center gap-2">
                      <span class="size-2 rounded-full bg-red-500"></span>
                      FALLA
                    </span>
                    <span class="font-semibold text-slate-700 dark:text-white">{{ eventosSeries.data[0] ?? 0 }}</span>
                  </div>
                  <div class="flex items-center justify-between gap-4">
                    <span class="inline-flex items-center gap-2">
                      <span class="size-2 rounded-full bg-amber-500"></span>
                      ANOMALÍA
                    </span>
                    <span class="font-semibold text-slate-700 dark:text-white">{{ eventosSeries.data[1] ?? 0 }}</span>
                  </div>
                  <div class="pt-2 text-[11px] text-slate-500 dark:text-[#92adc9]">
                    {{ eventosSeries.porcentaje ?? 0 }}% de entradas con impacto
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 dark:border-border-dark bg-white dark:bg-surface-dark p-6 shadow-sm">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Ubicaciones técnicas</p>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Top del mes</h3>
                </div>
                <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ widgetsPeriodoLabel }}</span>
              </div>
              <ul v-if="stats.top_ubicaciones?.length" class="mt-4 space-y-2">
                <li
                  v-for="item in stats.top_ubicaciones"
                  :key="item.id"
                  class="flex items-center justify-between rounded-xl border border-slate-200/70 dark:border-border-dark px-4 py-3"
                >
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ item.nombre }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-[#92adc9] truncate">{{ item.codigo }}</p>
                  </div>
                  <span class="inline-flex items-center rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 px-2 py-0.5 text-xs font-semibold">
                    {{ item.total }}
                  </span>
                </li>
              </ul>
              <p v-else class="mt-3 text-xs text-slate-500 dark:text-[#92adc9]">
                Sin ubicaciones registradas este mes.
              </p>
            </div>
          </div>
        </template>

        <template v-else-if="section === 'operativo_inventario'">
          <div class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200/70 dark:border-border-dark shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div>
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Entradas por mes</p>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Operativo vs Inventario ({{ seriesAnio }})</h2>
              </div>
                <div class="flex flex-wrap items-end gap-2 text-xs text-slate-500 dark:text-[#92adc9]">
                  <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 px-2 py-0.5 font-semibold">
                    Operativo
                  </span>
                  <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 font-semibold">
                    Inventario
                  </span>
                  <div class="ml-0 md:ml-4 flex flex-col">
                    <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Año</label>
                    <input
                      v-model.number.lazy="selectedYearDraft"
                      type="number"
                      min="2000"
                      max="2100"
                      class="mt-1 w-24 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2 py-1 text-xs font-semibold text-slate-700 dark:text-white"
                    />
                  </div>
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-primary text-white px-3 py-2 text-xs font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors"
                    @click="applyOperativoYear"
                  >
                    Aplicar año
                  </button>
                </div>
              </div>
            <div class="mt-4 h-[320px]">
              <canvas ref="chartCanvas"></canvas>
            </div>
          </div>
        </template>

        <template v-else-if="section === 'pm'">
          <div class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200/70 dark:border-border-dark shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div>
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Cuatrimestre</p>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ pmTitle }}</h2>
              </div>
              <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de orden</label>
                  <select
                    v-model="pmOrdenId"
                    class="mt-1 w-52 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  >
                    <option value="">Todas</option>
                    <option v-for="orden in pmOrdenes" :key="orden.id" :value="orden.id">
                      {{ orden.nombre }}
                    </option>
                  </select>
                </div>
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Año</label>
                  <input
                    v-model.number.lazy="pmAnioDraft"
                    type="number"
                    min="2000"
                    max="2100"
                    class="mt-1 w-24 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  />
                </div>
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Cuatrimestre</label>
                  <select
                    v-model.number="pmCuatDraft"
                    class="mt-1 w-28 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  >
                    <option :value="1">1 (Ene-Abr)</option>
                    <option :value="2">2 (May-Ago)</option>
                    <option :value="3">3 (Sep-Dic)</option>
                  </select>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-lg bg-primary text-white px-3 py-2 text-xs font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors"
                  @click="applyPmFecha"
                >
                  Aplicar fecha
                </button>
              </div>
            </div>
            <div class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
              {{ pmPeriodo }}
            </div>
            <div class="mt-4 h-[320px]">
              <canvas ref="ordenChartCanvas"></canvas>
            </div>
            <p v-if="!pmSeries.datasets.length" class="mt-3 text-xs text-slate-500 dark:text-[#92adc9]">
              Sin datos suficientes para mostrar la gráfica.
            </p>
          </div>
        </template>

        <template v-else-if="section === 'criterio'">
          <div class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200/70 dark:border-border-dark shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div>
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Cuatrimestre</p>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Entradas por criterio temático</h2>
              </div>
              <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Año</label>
                  <input
                    v-model.number.lazy="criterioAnioDraft"
                    type="number"
                    min="2000"
                    max="2100"
                    class="mt-1 w-24 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  />
                </div>
                <div class="flex flex-col">
                  <label class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Cuatrimestre</label>
                  <select
                    v-model.number="criterioCuatDraft"
                    class="mt-1 w-28 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-white"
                  >
                    <option :value="1">1 (Ene-Abr)</option>
                    <option :value="2">2 (May-Ago)</option>
                    <option :value="3">3 (Sep-Dic)</option>
                  </select>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-lg bg-primary text-white px-3 py-2 text-xs font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors"
                  @click="applyCriterioFecha"
                >
                  Aplicar fecha
                </button>
              </div>
            </div>
            <div class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
              Cuatrimestre {{ criterioSeries.cuatrimestre }} · {{ criterioSeries.anio }}
            </div>
            <div class="mt-4 h-[320px]">
              <canvas ref="criterioChartCanvas"></canvas>
            </div>
            <p v-if="!criterioSeries.datasets.length" class="mt-3 text-xs text-slate-500 dark:text-[#92adc9]">
              Sin datos suficientes para mostrar la gráfica.
            </p>
          </div>
        </template>

        <template v-else-if="section === 'usuarios'">
          <div class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200/70 dark:border-border-dark shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
              <div>
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Usuarios activos</p>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Actividad de publicación</h2>
              </div>
              <span class="text-xs text-slate-500 dark:text-[#92adc9]">Solo usuarios con estatus activo</span>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Más activos</h3>
                  <span class="text-[11px] uppercase tracking-wider text-slate-400 dark:text-[#92adc9]">Publicadas</span>
                </div>
                <ul v-if="stats.usuarios_activos_top?.length" class="space-y-2">
                  <li
                    v-for="item in stats.usuarios_activos_top"
                    :key="item.id"
                    class="flex items-center justify-between rounded-xl border border-slate-200/70 dark:border-border-dark px-4 py-3"
                  >
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ item.nombre }}</p>
                      <p class="text-[11px] text-slate-500 dark:text-[#92adc9] truncate">{{ item.correo }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 text-xs font-semibold">
                      {{ item.publicadas_count }}
                    </span>
                  </li>
                </ul>
                <p v-else class="text-xs text-slate-500 dark:text-[#92adc9]">Sin datos disponibles.</p>
              </div>

              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">Actividad baja</h3>
                  <span class="text-[11px] uppercase tracking-wider text-slate-400 dark:text-[#92adc9]">Publicadas</span>
                </div>
                <ul v-if="stats.usuarios_activos_baja?.length" class="space-y-2">
                  <li
                    v-for="item in stats.usuarios_activos_baja"
                    :key="item.id"
                    class="flex items-center justify-between rounded-xl border border-slate-200/70 dark:border-border-dark px-4 py-3"
                  >
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ item.nombre }}</p>
                      <p class="text-[11px] text-slate-500 dark:text-[#92adc9] truncate">{{ item.correo }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 text-xs font-semibold">
                      {{ item.publicadas_count }}
                    </span>
                  </li>
                </ul>
                <p v-else class="text-xs text-slate-500 dark:text-[#92adc9]">Sin datos disponibles.</p>
              </div>
            </div>
          </div>
        </template>
      </template>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import { fetchConfiguracionUsuario } from '../api/configuracion';
import {
  ArcElement,
  BarController,
  BarElement,
  CategoryScale,
  Chart,
  DoughnutController,
  Legend,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Tooltip,
} from 'chart.js';

Chart.register(
  ArcElement,
  BarController,
  BarElement,
  DoughnutController,
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Legend,
  Tooltip
);

const stats = ref({
  publicadas_mes: 0,
  publicadas_anio: 0,
  total: 0,
  publicadas: 0,
  borradores: 0,
  usuarios_activos_top: [],
  usuarios_activos_baja: [],
  widgets_periodo: {
    anio: new Date().getFullYear(),
    mes: new Date().getMonth() + 1,
  },
  impacto_severidad: {
    labels: [],
    data: [],
  },
  eventos_detectados: {
    labels: ['FALLA', 'ANOMALÍA'],
    data: [0, 0],
    porcentaje: 0,
    total_eventos: 0,
    total_impacto: 0,
  },
  top_ubicaciones: [],
  series: {
    labels: [],
    operativo: [],
    inventario: [],
    anio: new Date().getFullYear(),
    anio_actual: new Date().getFullYear(),
  },
  pm_series: {
    labels: [],
    datasets: [],
    mode: 'orden',
    anio: new Date().getFullYear(),
    cuatrimestre: 1,
  },
  criterio_series: {
    labels: [],
    datasets: [],
    anio: new Date().getFullYear(),
    cuatrimestre: 1,
  },
});
const error = ref('');
const chartCanvas = ref(null);
const ordenChartCanvas = ref(null);
const impactoChartCanvas = ref(null);
const eventosChartCanvas = ref(null);
const criterioChartCanvas = ref(null);
let chartInstance = null;
let ordenChartInstance = null;
let impactoChartInstance = null;
let eventosChartInstance = null;
let criterioChartInstance = null;
const selectedYear = ref(new Date().getFullYear());
const selectedYearDraft = ref(selectedYear.value);
const pmOrdenes = ref([]);
const pmOrdenId = ref('');
const pmAnioDraft = ref(new Date().getFullYear());
const pmCuatDraft = ref(Math.floor(new Date().getMonth() / 4) + 1);
const pmAnio = ref(pmAnioDraft.value);
const pmCuat = ref(pmCuatDraft.value);
const criterioAnio = ref(new Date().getFullYear());
const criterioCuat = ref(Math.floor(new Date().getMonth() / 4) + 1);
const criterioAnioDraft = ref(criterioAnio.value);
const criterioCuatDraft = ref(criterioCuat.value);
const widgetsAnio = ref(new Date().getFullYear());
const widgetsMes = ref(new Date().getMonth() + 1);
const widgetsAnioDraft = ref(widgetsAnio.value);
const widgetsMesDraft = ref(widgetsMes.value);
const defaultWidgetOrder = ['resumen', 'mensuales', 'operativo_inventario', 'pm', 'criterio', 'usuarios'];
const widgetOrder = ref([...defaultWidgetOrder]);

const orderedSections = computed(() => widgetOrder.value);

const mesLabel = computed(() =>
  new Intl.DateTimeFormat('es-MX', { month: 'long' }).format(new Date())
);
const anioLabel = computed(() => new Date().getFullYear());
const seriesAnio = computed(() => stats.value?.series?.anio ?? anioLabel.value);
const pmSeries = computed(() => stats.value?.pm_series ?? { labels: [], datasets: [], mode: 'orden', anio: pmAnio.value, cuatrimestre: pmCuat.value });
const criterioSeries = computed(() => stats.value?.criterio_series ?? { labels: [], datasets: [], anio: criterioAnio.value, cuatrimestre: criterioCuat.value });
const impactoSeries = computed(() => stats.value?.impacto_severidad ?? { labels: [], data: [] });
const eventosSeries = computed(() => stats.value?.eventos_detectados ?? { labels: [], data: [0, 0], porcentaje: 0 });
const widgetsPeriodoLabel = computed(() => {
  const date = new Date(widgetsAnio.value, widgetsMes.value - 1, 1);
  return new Intl.DateTimeFormat('es-MX', { month: 'long', year: 'numeric' }).format(date);
});

const normalizeWidgetOrder = (value) => {
  const parsed = String(value ?? '')
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);

  const unique = [];
  parsed.forEach((item) => {
    if (defaultWidgetOrder.includes(item) && !unique.includes(item)) {
      unique.push(item);
    }
  });

  defaultWidgetOrder.forEach((item) => {
    if (!unique.includes(item)) {
      unique.push(item);
    }
  });

  return unique;
};
const pmOrdenNombre = computed(() => {
  const orden = pmOrdenes.value.find((item) => String(item.id) === String(pmOrdenId.value));
  return orden?.nombre || '';
});
const pmTitle = computed(() => (pmOrdenId.value ? `Entradas por clase de actividad · ${pmOrdenNombre.value}` : 'Entradas por clase de orden'));
const pmPeriodo = computed(() => `Cuatrimestre ${pmSeries.value.cuatrimestre} · ${pmSeries.value.anio}`);
const renderChart = () => {
  if (!chartCanvas.value) return;
  const labels = stats.value.series?.labels ?? [];
  const operativo = stats.value.series?.operativo ?? [];
  const inventario = stats.value.series?.inventario ?? [];

  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Operativo',
          data: operativo,
          backgroundColor: 'rgba(59, 130, 246, 0.55)',
          borderRadius: 6,
        },
        {
          label: 'Inventario',
          data: inventario,
          backgroundColor: 'rgba(245, 158, 11, 0.55)',
          borderRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.15)' },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { size: 12 },
          bodyFont: { size: 11 },
          padding: 10,
        },
      },
    },
  });
};

const renderOrdenChart = () => {
  if (!ordenChartCanvas.value) return;
  const labels = pmSeries.value.labels ?? [];
  const datasets = pmSeries.value.datasets ?? [];
  const palette = [
    'rgba(59, 130, 246, 0.55)',
    'rgba(245, 158, 11, 0.55)',
    'rgba(16, 185, 129, 0.55)',
    'rgba(99, 102, 241, 0.55)',
    'rgba(244, 63, 94, 0.55)',
    'rgba(34, 211, 238, 0.55)',
    'rgba(168, 85, 247, 0.55)',
  ];

  if (ordenChartInstance) {
    ordenChartInstance.destroy();
  }

  ordenChartInstance = new Chart(ordenChartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: datasets.map((item, index) => ({
        label: item.label,
        data: item.data,
        backgroundColor: palette[index % palette.length],
        borderRadius: 6,
      })),
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          stacked: false,
          grid: { display: false },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
        y: {
          stacked: false,
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.15)' },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
      },
      plugins: {
        legend: { position: 'bottom', labels: { color: '#94a3b8', boxWidth: 10, font: { size: 10 } } },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { size: 12 },
          bodyFont: { size: 11 },
          padding: 10,
        },
      },
    },
  });
};

const renderImpactoChart = () => {
  if (!impactoChartCanvas.value) return;
  const labels = impactoSeries.value.labels ?? [];
  const data = impactoSeries.value.data ?? [];

  if (impactoChartInstance) {
    impactoChartInstance.destroy();
  }

  impactoChartInstance = new Chart(impactoChartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          data,
          backgroundColor: 'rgba(99, 102, 241, 0.55)',
          borderRadius: 6,
        },
      ],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.15)' },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
        y: {
          grid: { display: false },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { size: 12 },
          bodyFont: { size: 11 },
          padding: 10,
        },
      },
    },
  });
};

const renderEventosChart = () => {
  if (!eventosChartCanvas.value) return;
  const labels = eventosSeries.value.labels ?? ['FALLA', 'ANOMALÍA'];
  const data = eventosSeries.value.data ?? [0, 0];

  if (eventosChartInstance) {
    eventosChartInstance.destroy();
  }

  eventosChartInstance = new Chart(eventosChartCanvas.value, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [
        {
          data,
          backgroundColor: ['rgba(239, 68, 68, 0.7)', 'rgba(245, 158, 11, 0.7)'],
          borderWidth: 0,
        },
      ],
    },
    options: {
      cutout: '65%',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { size: 12 },
          bodyFont: { size: 11 },
          padding: 10,
        },
      },
    },
  });
};

const renderCriterioChart = () => {
  if (!criterioChartCanvas.value) return;
  const labels = criterioSeries.value.labels ?? [];
  const datasets = criterioSeries.value.datasets ?? [];
  const palette = [
    'rgba(59, 130, 246, 0.55)',
    'rgba(245, 158, 11, 0.55)',
    'rgba(16, 185, 129, 0.55)',
    'rgba(99, 102, 241, 0.55)',
    'rgba(244, 63, 94, 0.55)',
    'rgba(34, 211, 238, 0.55)',
    'rgba(168, 85, 247, 0.55)',
  ];

  if (criterioChartInstance) {
    criterioChartInstance.destroy();
  }

  criterioChartInstance = new Chart(criterioChartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: datasets.map((item, index) => ({
        label: item.label,
        data: item.data,
        backgroundColor: palette[index % palette.length],
        borderRadius: 6,
      })),
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          stacked: false,
          grid: { display: false },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
        y: {
          stacked: false,
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.15)' },
          ticks: { color: '#94a3b8', font: { size: 11 } },
        },
      },
      plugins: {
        legend: { position: 'bottom', labels: { color: '#94a3b8', boxWidth: 10, font: { size: 10 } } },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { size: 12 },
          bodyFont: { size: 11 },
          padding: 10,
        },
      },
    },
  });
};

const loadStats = async () => {
  try {
    const response = await window.axios.get('/api/v1/dashboard/resumen', {
      params: {
        anio: selectedYear.value,
        pm_anio: pmAnio.value,
        pm_cuatrimestre: pmCuat.value,
        pm_orden_id: pmOrdenId.value || undefined,
        criterio_anio: criterioAnio.value,
        criterio_cuatrimestre: criterioCuat.value,
        widgets_anio: widgetsAnio.value,
        widgets_mes: widgetsMes.value,
      },
    });
    stats.value = response.data ?? stats.value;
    if (stats.value?.series?.anio) {
      selectedYear.value = stats.value.series.anio;
      selectedYearDraft.value = stats.value.series.anio;
    }
    if (stats.value?.pm_series?.anio) {
      pmAnio.value = stats.value.pm_series.anio;
      pmCuat.value = stats.value.pm_series.cuatrimestre;
      pmAnioDraft.value = pmAnio.value;
      pmCuatDraft.value = pmCuat.value;
    }
    if (stats.value?.criterio_series?.anio) {
      criterioAnio.value = stats.value.criterio_series.anio;
      criterioCuat.value = stats.value.criterio_series.cuatrimestre;
      criterioAnioDraft.value = criterioAnio.value;
      criterioCuatDraft.value = criterioCuat.value;
    }
    if (stats.value?.widgets_periodo?.anio) {
      widgetsAnio.value = stats.value.widgets_periodo.anio;
      widgetsMes.value = stats.value.widgets_periodo.mes;
      widgetsAnioDraft.value = widgetsAnio.value;
      widgetsMesDraft.value = widgetsMes.value;
    }
    renderChart();
    renderOrdenChart();
    renderImpactoChart();
    renderEventosChart();
    renderCriterioChart();
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudo cargar el resumen.';
  }
};

const loadWidgetOrder = async () => {
  try {
    const items = await fetchConfiguracionUsuario();
    const value = Array.isArray(items)
      ? items.find((item) => item.clave === 'dashboard.widgets_order')?.valor
      : null;
    if (value) {
      widgetOrder.value = normalizeWidgetOrder(value);
    }
  } catch {
    widgetOrder.value = [...defaultWidgetOrder];
  }
};

onMounted(async () => {
  await loadStats();
  await loadPmOrdenes();
  await loadWidgetOrder();
});

const applyPmFecha = () => {
  if (!pmAnioDraft.value || pmCuatDraft.value < 1 || pmCuatDraft.value > 3) return;
  pmAnio.value = pmAnioDraft.value;
  pmCuat.value = pmCuatDraft.value;
  loadStats();
};

const applyOperativoYear = () => {
  if (!selectedYearDraft.value) return;
  selectedYear.value = selectedYearDraft.value;
  loadStats();
};

const applyWidgetsPeriodo = () => {
  if (!widgetsAnioDraft.value || widgetsMesDraft.value < 1 || widgetsMesDraft.value > 12) return;
  widgetsAnio.value = widgetsAnioDraft.value;
  widgetsMes.value = widgetsMesDraft.value;
  loadStats();
};

const applyCriterioFecha = () => {
  if (!criterioAnioDraft.value || criterioCuatDraft.value < 1 || criterioCuatDraft.value > 3) return;
  criterioAnio.value = criterioAnioDraft.value;
  criterioCuat.value = criterioCuatDraft.value;
  loadStats();
};

const loadPmOrdenes = async () => {
  try {
    const response = await window.axios.get('/api/v1/catalogos/pm/ordenes');
    pmOrdenes.value = response.data?.data ?? response.data;
  } catch {
    pmOrdenes.value = [];
  }
};

watch(pmOrdenId, () => {
  loadStats();
});

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
  if (ordenChartInstance) {
    ordenChartInstance.destroy();
    ordenChartInstance = null;
  }
  if (impactoChartInstance) {
    impactoChartInstance.destroy();
    impactoChartInstance = null;
  }
  if (eventosChartInstance) {
    eventosChartInstance.destroy();
    eventosChartInstance = null;
  }
  if (criterioChartInstance) {
    criterioChartInstance.destroy();
    criterioChartInstance = null;
  }
});
</script>
