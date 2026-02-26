<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div
        v-if="loadingEntrada"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
      >
        <div class="rounded-2xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark px-6 py-5 shadow-xl flex items-center gap-4">
          <span class="material-symbols-outlined text-primary text-2xl animate-spin">progress_activity</span>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Cargando entrada</p>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Aplicando datos en el formulario...</p>
          </div>
        </div>
      </div>
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Operación</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Nueva Entrada</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ headerTitle }}</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">{{ headerSubtitle }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25 disabled:opacity-60"
            :disabled="saving || loadingEntrada"
            @click="guardarEntrada(false)"
          >
            <span class="material-symbols-outlined">save</span>
            <span>{{ saveLabel }}</span>
          </button>
          <button
            class="inline-flex items-center gap-2 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9] px-4 py-2.5 rounded-lg font-bold text-sm transition-all hover:bg-slate-50 dark:hover:bg-border-dark disabled:opacity-60"
            :disabled="saving || loadingEntrada"
            @click="guardarEntrada(true)"
          >
            <span class="material-symbols-outlined">public</span>
            <span>Publicar</span>
          </button>
          <button
            v-if="isEditMode && entradaPublicada"
            class="inline-flex items-center gap-2 border border-amber-300 text-amber-700 px-4 py-2.5 rounded-lg font-bold text-sm transition-all hover:bg-amber-50 disabled:opacity-60"
            :disabled="saving || loadingEntrada"
            @click="despublicarEntrada"
          >
            <span class="material-symbols-outlined">unpublished</span>
            <span>Pasar a borrador</span>
          </button>
        </div>
      </div>

      <div v-if="saveError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ saveError }}
      </div>
      <div v-if="saveSuccess" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ saveSuccess }}
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div>
              <h4 class="text-lg font-bold text-slate-900 dark:text-white">Datos Generales</h4>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Título</label>
              <input
                v-model="form.titulo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                placeholder="Resumen breve de la entrada"
              />
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Fecha inicio</label>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <input
                    v-model="fechaInicioDate"
                    type="date"
                    class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                  />
                  <input
                    v-model="fechaInicioTime"
                    type="time"
                    step="60"
                    class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                    :disabled="!fechaInicioDate"
                  />
                </div>
              </div>
              <div class="flex-1">
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Fecha fin</label>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <input
                    v-model="fechaFinDate"
                    type="date"
                    class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                  />
                  <input
                    v-model="fechaFinTime"
                    type="time"
                    step="60"
                    class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                    :disabled="!fechaFinDate"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Referencias a Sistemas Externos</h4>
                <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Eventos RNFO, Incidentes, Licencias, etc.</p>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                {{ referenciasExternas.length }} referencias
              </span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                  <tr>
                    <th class="px-4 py-3">Sistema</th>
                    <th class="px-4 py-3">Folio</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                  <tr>
                    <td class="px-4 py-3">
                      <select
                        v-model="referenciaForm.sistema_id"
                        class="w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                        :class="referenciaForm.sistema_id ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
                        @change="onReferenciaSistemaChange"
                      >
                        <option value="">Selecciona un sistema</option>
                        <option v-for="sistema in sistemasExternosActivos" :key="sistema.id" :value="sistema.id">
                          {{ sistema.nombre }}
                        </option>
                      </select>
                    </td>
                    <td class="px-4 py-3">
                      <input
                        v-model="referenciaForm.externo_id"
                        type="text"
                        class="w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                        :class="referenciaValida ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
                        @input="onReferenciaFormInput"
                        :placeholder="demoPlaceholder(referenciaForm)"
                      />
                      <p v-if="referenciaForm.error" class="mt-1 text-[11px] text-red-500">{{ referenciaForm.error }}</p>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <button
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors bg-primary text-white hover:bg-primary/90"
                        type="button"
                        @click="addReferenciaExterna"
                      >
                        <span class="material-symbols-outlined text-sm">add</span>
                        Agregar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p v-if="referenciaForm.patron_regex" class="text-[11px] text-slate-500 dark:text-[#92adc9]">
              Patrón ({{ referenciaSistemaNombre }}): {{ referenciaForm.patron_regex }}
            </p>

            <div v-if="referenciasExternas.length === 0" class="text-xs text-slate-500 dark:text-[#92adc9]">
              No hay referencias agregadas.
            </div>

            <div v-if="referenciasExternas.length" class="flex flex-wrap gap-2">
              <span
                v-for="ref in referenciasExternas"
                :key="ref.id"
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary"
              >
                {{ ref.sistema_nombre }} · {{ ref.externo_id }}
                <button type="button" class="text-primary hover:text-primary/70" @click="removeReferenciaExterna(ref.id)">
                  <span class="material-symbols-outlined text-sm">close</span>
                </button>
              </span>
            </div>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Inventario NMS/EMS</h4>
                <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Busca elementos y agrégalos a la entrada.</p>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                {{ inventarioSeleccionados.length }} seleccionados
              </span>
            </div>

            <input
              v-model="inventarioQuery"
              type="text"
              class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
              placeholder="Buscar por código, nombre o tipo..."
            />

            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
                :class="inventarioTipoFiltro === '' ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-border-dark/40 text-slate-600 dark:text-[#92adc9] border-slate-200 dark:border-border-dark hover:text-primary'"
                @click="inventarioTipoFiltro = ''"
              >
                Todos
              </button>
              <button
                v-for="tipo in inventarioTiposFiltro"
                :key="tipo.value"
                type="button"
                class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
                :class="inventarioTipoFiltro === tipo.value ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-border-dark/40 text-slate-600 dark:text-[#92adc9] border-slate-200 dark:border-border-dark hover:text-primary'"
                @click="inventarioTipoFiltro = tipo.value"
              >
                {{ tipo.label }}
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                  <tr>
                    <th class="px-4 py-3">Elemento</th>
                    <th class="px-4 py-3">Red</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                  <tr v-for="elemento in inventarioPaginado" :key="elemento.id">
                    <td class="px-4 py-3">
                      <div class="text-sm font-medium">{{ elemento.nombre || 'Sin nombre' }}</div>
                      <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ elemento.codigo }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                      {{ elemento.red?.nombre || '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      {{ elemento.tipo }}
                    </td>
                    <td class="px-4 py-3 text-right">
                      <button
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                        :class="isInventarioSelected(elemento.id)
                          ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                          : 'bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9] hover:text-primary'"
                        type="button"
                        @click="toggleInventario(elemento)"
                      >
                        <span class="material-symbols-outlined text-sm">
                          {{ isInventarioSelected(elemento.id) ? 'check_circle' : 'add' }}
                        </span>
                        {{ isInventarioSelected(elemento.id) ? 'Agregado' : 'Agregar' }}
                      </button>
                    </td>
                  </tr>
                  <tr v-if="inventarioFiltrado.length === 0">
                    <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500 dark:text-[#92adc9]">
                      Sin elementos de inventario disponibles.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <PaginationBar
              :page="inventarioPage"
              :total-pages="inventarioTotalPages"
              :range-start="inventarioRange.start"
              :range-end="inventarioRange.end"
              :total-items="inventarioFiltrado.length"
              @update:page="inventarioPage = $event"
            />

            <div v-if="inventarioSeleccionados.length" class="flex flex-wrap gap-2">
              <div
                v-for="elemento in inventarioSeleccionados"
                :key="elemento.id"
                class="relative group"
              >
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                  {{ elemento.nombre || elemento.codigo }}
                  <button type="button" class="text-primary hover:text-primary/70" @click="toggleInventario(elemento)">
                    <span class="material-symbols-outlined text-sm">close</span>
                  </button>
                </span>
                <div
                  v-if="hasExtensionTable(elemento.tipo)"
                  class="pointer-events-none absolute left-0 top-full mt-2 z-20 w-80 max-w-[calc(100vw-5rem)] rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-[#16222e] p-3 shadow-xl opacity-0 translate-y-1 transition-all duration-150 group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:translate-y-0"
                >
                  <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">
                    {{ extensionTableName(elemento.tipo) }}
                  </p>
                  <div v-if="extensionTooltipRows(elemento).length" class="mt-2 space-y-1.5">
                    <div
                      v-for="item in extensionTooltipRows(elemento)"
                      :key="`${elemento.id}-${item.key}`"
                      class="flex items-start justify-between gap-2 text-xs"
                    >
                      <span class="font-semibold text-slate-600 dark:text-[#92adc9]">{{ item.label }}</span>
                      <span class="text-right text-slate-900 dark:text-white break-all">{{ item.value }}</span>
                    </div>
                  </div>
                  <p v-else class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                    Sin datos de extensión disponibles.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Resumen técnico</h4>
                <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Generado automáticamente con los datos seleccionados.</p>
              </div>
              <span class="text-[11px] text-slate-500 dark:text-[#92adc9] uppercase tracking-wider">Solo lectura</span>
            </div>
            <textarea
              :value="resumenTecnicoPreview"
              readonly
              rows="6"
              class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-3 text-sm text-slate-900 dark:text-white"
            ></textarea>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Clasificación Técnica</h4>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Criterio Temático</label>
              <select
                v-model="form.entrada_criterio_id"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="form.entrada_criterio_id ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
              >
                <option value="">Selecciona un tema</option>
                <option v-for="criterio in criterios" :key="criterio.id" :value="criterio.id">
                  {{ criterio.nombre }}
                </option>
              </select>
              <p v-if="criterioSeleccionado" class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                {{ criterioSeleccionado.descripcion || 'Sin descripción registrada para este criterio.' }}
              </p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nivel de Impacto</label>
              <select
                v-model="form.entrada_impacto_id"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="form.entrada_impacto_id ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
                @change="onImpactoChange"
              >
                <option value="">Selecciona un impacto</option>
                <option v-for="impacto in impactos" :key="impacto.id" :value="impacto.id">
                  {{ impacto.nombre }}
                </option>
              </select>
              <p v-if="impactoSeleccionado" class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                {{ impactoSeleccionado.descripcion || 'Sin descripción registrada para este impacto.' }}
              </p>
            </div>
            <div v-if="form.entrada_impacto_id && !isSinImpacto">
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Evento detectado</label>
              <select
                v-model="form.tipo_evento"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="eventoRequerido && !form.tipo_evento ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              >
                <option value="">Selecciona evento</option>
                <option value="FALLA">FALLA</option>
                <option value="ANOMALIA">ANOMALIA</option>
              </select>
              <p class="ml-2 mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                Con impacto → requiere FALLA o ANOMALIA.
              </p>
              <p class="ml-3 mt-1 text-[11px] text-slate-500 dark:text-[#92adc9]">
                <span class="font-semibold">FALLA:</span> interrupción o indisponibilidad del servicio.
                <span class="font-semibold">ANOMALÍA:</span> condición irregular sin caída total.
              </p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Tipo de registro</label>
              <select
                v-model="form.tipo_registro"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="form.tipo_registro ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
              >
                <option value="">Selecciona tipo</option>
                <option value="operativo">Operativo</option>
                <option value="inventario">Inventario</option>
              </select>
              <p class="ml-4 mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                <span class="font-semibold">Operativo:</span> Eventos, fallas o actividades del turno. 
                <span class="font-semibold">Inventario:</span> Altas, bajas o cambios de elementos NMS/EMS.
              </p>
            </div>
            <div v-if="form.tipo_registro === 'inventario'">
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Acción de inventario</label>
              <select
                v-model="form.accion_inventario"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="form.accion_inventario ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
              >
                <option value="">Selecciona acción</option>
                <option value="alta">Alta</option>
                <option value="baja">Baja</option>
                <option value="cambio">Cambio</option>
              </select>
              <p class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                Define el tipo de movimiento que se aplicará al inventario.
              </p>
            </div>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white">Ubicación Técnica y Equipo</h4>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-[#92adc9]">
                <input v-model="ubicacionModo" type="radio" value="sap" class="text-primary focus:ring-primary" />
                Ubicación técnica (SAP-Like)
              </label>
              <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-[#92adc9]">
                <input v-model="ubicacionModo" type="radio" value="manual" class="text-primary focus:ring-primary" />
                Captura manual
              </label>
            </div>

            <div v-if="ubicacionModo === 'sap'" class="space-y-3">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nivel 1</label>
                  <select
                    v-model="ubicacionFiltros.nivel_1"
                    class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                  >
                    <option value="">Selecciona nivel 1</option>
                    <option v-for="nivel in nivelesUbicacion.nivel_1" :key="nivel" :value="nivel">
                      {{ nivel }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nivel 2</label>
                  <select
                    v-model="ubicacionFiltros.nivel_2"
                    class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                    :disabled="!ubicacionFiltros.nivel_1 || nivelesUbicacion.nivel_2.length === 0"
                  >
                    <option value="">Todos</option>
                    <option v-for="nivel in nivelesUbicacion.nivel_2" :key="nivel" :value="nivel">
                      {{ nivel }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nivel 3</label>
                  <select
                    v-model="ubicacionFiltros.nivel_3"
                    class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                    :disabled="!ubicacionFiltros.nivel_2 || nivelesUbicacion.nivel_3.length === 0"
                  >
                    <option value="">Todos</option>
                    <option v-for="nivel in nivelesUbicacion.nivel_3" :key="nivel" :value="nivel">
                      {{ nivel }}
                    </option>
                  </select>
                </div>
              </div>

              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Buscar ubicación</label>
                <input
                  v-model.trim="ubicacionFiltros.q"
                  type="text"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                  placeholder="Texto en código, nombre o cualquier nivel"
                  :disabled="!ubicacionFiltros.nivel_1"
                />
              </div>

              <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 dark:text-[#92adc9]">
                  <template v-if="!ubicacionFiltros.nivel_1">
                    Selecciona nivel 1 para cargar ubicaciones.
                  </template>
                  <template v-else-if="loadingUbicaciones">
                    Cargando ubicaciones...
                  </template>
                  <template v-else-if="ubicacionesTotalEncontradas > ubicacionesFlat.length">
                    Se muestran {{ ubicacionesFlat.length }} de {{ ubicacionesTotalEncontradas }} ubicaciones (límite de carga). Ajusta los filtros para acotar resultados.
                  </template>
                  <template v-else>
                    {{ ubicacionesFlat.length }} ubicación(es) encontradas.
                  </template>
                </p>
                <button
                  type="button"
                  class="text-xs font-semibold text-primary hover:underline disabled:opacity-50"
                  :disabled="!ubicacionFiltros.nivel_1 && !ubicacionFiltros.nivel_2 && !ubicacionFiltros.nivel_3 && !ubicacionFiltros.q"
                  @click="clearUbicacionFiltros"
                >
                  Limpiar filtros
                </button>
              </div>

              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Ubicación técnica (SAP-Like)</label>
                <select
                  v-model="form.ubicacion_tecnica_id"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm transition-all"
                  :class="form.ubicacion_tecnica_id ? 'border-primary ring-2 ring-primary/20' : 'border-slate-200 dark:border-border-dark'"
                  :disabled="!ubicacionFiltros.nivel_1 || loadingUbicaciones || ubicacionesFlat.length === 0"
                >
                  <option value="">Selecciona una ubicación</option>
                  <option v-for="u in ubicacionesFlat" :key="u.id" :value="u.id">
                    {{ u.label }}
                  </option>
                </select>
                <div v-if="ubicacionSeleccionada?.codigo" class="mt-2">
                  <p class="text-xs text-slate-500 dark:text-[#92adc9] mb-1">Código de ubicación seleccionado:</p>
                  <UbicacionCodigoTooltip
                    :ubicacion="ubicacionSeleccionada"
                    :resolve-detalle="resolveDetalleNivelUbicacion"
                  />
                </div>
              </div>
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Buscar equipo</label>
                <input
                  v-model.trim="equipoFiltros.q"
                  type="text"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                  placeholder="Código o nombre del equipo"
                />
              </div>
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Equipo</label>
                <div class="mt-2 flex items-center gap-2">
                  <select
                    v-model="form.equipo_id"
                    class="w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm transition-all"
                    :class="form.equipos.length ? 'border-primary ring-2 ring-primary/20' : 'border-slate-200 dark:border-border-dark'"
                    :disabled="loadingEquipos || equiposFiltrados.length === 0"
                  >
                    <option value="">Selecciona un equipo</option>
                    <option v-for="equipo in equiposFiltrados" :key="equipo.id" :value="equipo.id">
                      {{ equipo.nombre }} ({{ equipo.codigo }})
                    </option>
                  </select>
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition-colors"
                    :class="form.equipo_id ? 'bg-primary text-white hover:bg-primary/90' : 'bg-slate-100 text-slate-500 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    :disabled="!form.equipo_id"
                    @click="addEquipoSeleccionado"
                  >
                    <span class="material-symbols-outlined text-sm">add</span>
                    Agregar equipo
                  </button>
                </div>
                <p v-if="loadingEquipos" class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                  Cargando equipos...
                </p>
                <p v-else-if="!form.ubicacion_tecnica_id && !equipoFiltros.q" class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                  Busca un equipo por código o nombre, o selecciona una ubicación.
                </p>
                <p v-else-if="form.ubicacion_tecnica_id && !equipoFiltros.q && equiposFiltrados.length === 0" class="mt-2 text-xs text-amber-500">
                  No hay equipos activos para la ubicación seleccionada.
                </p>
                <p v-else-if="equipoFiltros.q && equiposFiltrados.length === 0" class="mt-2 text-xs text-amber-500">
                  No se encontraron equipos con ese criterio.
                </p>
                <div v-if="equiposSeleccionados.length" class="mt-3 flex flex-wrap gap-2">
                  <span
                    v-for="equipo in equiposSeleccionados"
                    :key="equipo.id"
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary"
                  >
                    {{ equipo.nombre || 'Equipo' }}<span v-if="equipo.codigo"> · {{ equipo.codigo }}</span>
                    <button type="button" class="text-primary hover:text-primary/70" @click="removeEquipoSeleccionado(equipo.id)">
                      <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                  </span>
                </div>
              </div>
            </div>

            <div v-else class="space-y-3">
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Ubicación manual</label>
                <input
                  v-model="form.ubicacion_manual"
                  type="text"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                  placeholder="Ej. Torre 12, sitio remoto, etc."
                />
              </div>
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Equipo manual</label>
                <input
                  v-model="form.equipo_manual"
                  type="text"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                  placeholder="Ej. Radio X, router Y, etc."
                />
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-3">
            <div>
              <h4 class="text-lg font-bold text-slate-900 dark:text-white">Orden de Actividad</h4>
              <span class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">SAP-Like</span>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de orden</label>
              <select
                v-model="form.pm_clase_orden_id"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="form.pm_clase_orden_id ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
                @change="onOrdenChange"
              >
                <option value="">Selecciona</option>
                <option v-for="orden in pmOrdenesActivas" :key="orden.id" :value="orden.id">
                  {{ orden.nombre }}
                </option>
              </select>
              <p v-if="pmOrdenesActivas.length === 0" class="mt-2 text-xs text-amber-500">
                No hay clases de orden activas disponibles.
              </p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de actividad</label>
              <select
                v-model="form.pm_clase_actividad_id"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="form.pm_clase_actividad_id ? 'border-slate-200 dark:border-border-dark' : 'border-red-400 dark:border-red-500'"
                :disabled="filteredActividades.length === 0"
              >
                <option value="">Selecciona</option>
                <option v-for="actividad in filteredActividades" :key="actividad.id" :value="actividad.id">
                  {{ actividad.nombre }}
                </option>
              </select>
              <p v-if="form.pm_clase_orden_id && filteredActividades.length === 0" class="mt-2 text-xs text-amber-500">
                No hay actividades activas disponibles para la orden seleccionada.
              </p>
            </div>
          </div>

        </div>
      </div>

      <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Descripción</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Editor WYSIWYG (HTML + texto plano).</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-border-dark transition-colors" @click.prevent="exec('bold')">
              <span class="material-symbols-outlined text-lg">format_bold</span>
            </button>
            <button type="button" class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-border-dark transition-colors" @click.prevent="exec('italic')">
              <span class="material-symbols-outlined text-lg">format_italic</span>
            </button>
            <button type="button" class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-border-dark transition-colors" @click.prevent="exec('insertUnorderedList')">
              <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
            </button>
            <button type="button" class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-border-dark transition-colors" @click.prevent="exec('insertOrderedList')">
              <span class="material-symbols-outlined text-lg">format_list_numbered</span>
            </button>
            <button
              class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-border-dark transition-colors"
              type="button"
              @click="openImageDialog"
              :disabled="uploadingImage"
              title="Subir imagen"
            >
              <span class="material-symbols-outlined text-lg">image</span>
            </button>
            <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="onImageSelected" />
            <button
              class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-border-dark transition-colors disabled:opacity-60"
              type="button"
              @click="openFileDialog"
              :disabled="uploadingFile || archivosPermitidos.length === 0"
              title="Subir archivo"
            >
              <span class="material-symbols-outlined text-lg">upload_file</span>
            </button>
            <input ref="fileInput" type="file" :accept="archivoAccept" class="hidden" multiple @change="onFileSelected" />
          </div>
        </div>

        <div class="entrada-editor min-h-[260px] bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-3 text-sm text-slate-900 dark:text-white">
          <EditorContent
            :editor="editor"
            class="[&_.ProseMirror]:min-h-[236px] [&_.ProseMirror]:outline-none [&_.ProseMirror_img]:max-w-full [&_.ProseMirror_img]:h-auto [&_.ProseMirror_img]:rounded-lg [&_.ProseMirror_img]:my-2"
            @paste="handlePaste"
          />
        </div>
        <p v-if="uploadingImage" class="text-xs text-slate-500 dark:text-[#92adc9]">Subiendo imagen...</p>
        <p v-if="uploadError" class="text-xs text-red-500">{{ uploadError }}</p>
        <p v-if="uploadingFile" class="text-xs text-slate-500 dark:text-[#92adc9]">Subiendo archivo...</p>
        <p v-if="uploadFileError" class="text-xs text-red-500">{{ uploadFileError }}</p>

        <p class="text-xs text-slate-500 dark:text-[#92adc9]">
          Puedes pegar imágenes desde el portapapeles o subirlas desde tu equipo. Se convierten automáticamente a PNG.
        </p>
        <p class="text-xs text-slate-500 dark:text-[#92adc9]">
          Adjuntos: máximo {{ config.max_adjuntos_por_entrada || '15' }} archivos.
          Tamaño máximo por imagen: {{ config.imagenes_max_mb || '2' }} MB.
          Tamaño máximo por archivo: {{ archivoMaxMb }} MB.
          Tipos permitidos: {{ archivosPermitidosLabel }}.
        </p>

        <div v-if="archivosAdjuntos.length" class="flex flex-wrap gap-2">
          <span
            v-for="file in archivosAdjuntos"
            :key="file.id"
            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-border-dark/60 text-slate-700 dark:text-[#92adc9]"
          >
            <span class="material-symbols-outlined text-sm">description</span>
            {{ file.nombre_original || file.ruta }}
            <button type="button" class="text-slate-500 hover:text-slate-700 dark:text-[#92adc9]" @click="removeArchivoAdjunto(file)">
              <span class="material-symbols-outlined text-sm">close</span>
            </button>
          </span>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import PaginationBar from '../components/PaginationBar.vue';
import UbicacionCodigoTooltip from '../components/UbicacionCodigoTooltip.vue';
import { fetchConfiguracionSistema } from '../api/configuracion';

const form = reactive({
  titulo: '',
  fecha_inicio: '',
  fecha_fin: '',
  cuerpo_html: '',
  cuerpo_texto: '',
  pm_clase_orden_id: '',
  pm_clase_actividad_id: '',
  entrada_criterio_id: '',
  entrada_impacto_id: '',
  tipo_registro: '',
  accion_inventario: '',
  tipo_evento: '',
  ubicacion_tecnica_id: '',
  equipo_id: '',
  equipos: [],
  ubicacion_manual: '',
  equipo_manual: '',
  inv_elementos: [],
  adjuntos: [],
});

const splitDatetimeLocal = (value) => {
  const source = String(value || '').trim();
  if (!source) return { date: '', time: '' };
  const [datePart = '', rawTime = ''] = source.split('T');
  const timePart = rawTime.slice(0, 5);
  return {
    date: datePart,
    time: timePart,
  };
};

const mergeDatetimeLocal = (datePart, timePart) => {
  if (!datePart) return '';
  return `${datePart}T${timePart || '00:00'}`;
};

const fechaInicioDate = computed({
  get() {
    return splitDatetimeLocal(form.fecha_inicio).date;
  },
  set(value) {
    const current = splitDatetimeLocal(form.fecha_inicio);
    form.fecha_inicio = mergeDatetimeLocal(value, current.time);
  },
});

const fechaInicioTime = computed({
  get() {
    return splitDatetimeLocal(form.fecha_inicio).time;
  },
  set(value) {
    const current = splitDatetimeLocal(form.fecha_inicio);
    form.fecha_inicio = mergeDatetimeLocal(current.date, value);
  },
});

const fechaFinDate = computed({
  get() {
    return splitDatetimeLocal(form.fecha_fin).date;
  },
  set(value) {
    const current = splitDatetimeLocal(form.fecha_fin);
    form.fecha_fin = mergeDatetimeLocal(value, current.time);
  },
});

const fechaFinTime = computed({
  get() {
    return splitDatetimeLocal(form.fecha_fin).time;
  },
  set(value) {
    const current = splitDatetimeLocal(form.fecha_fin);
    form.fecha_fin = mergeDatetimeLocal(current.date, value);
  },
});

const config = reactive({
  max_adjuntos_por_entrada: null,
  imagenes_max_mb: null,
  archivos_max_mb: null,
  archivos_permitidos: {
    pdf: true,
    doc: true,
    docx: true,
    xls: true,
    xlsx: true,
  },
});

const isEditMode = computed(() => !!route.params.id);
const headerTitle = computed(() => (isEditMode.value ? 'Editar Entrada de Bitácora' : 'Nueva Entrada de Bitácora'));
const headerSubtitle = computed(() =>
  isEditMode.value ? 'Actualiza la información de la entrada seleccionada.' : 'Registra un evento operativo, mantenimiento o inventario.'
);
const saveLabel = computed(() => (isEditMode.value ? 'Guardar cambios' : 'Guardar borrador'));

const router = useRouter();
const route = useRoute();
const editor = ref(null);
const imageInput = ref(null);
const fileInput = ref(null);
const pmOrdenes = ref([]);
const pmActividades = ref([]);
const pmMatriz = ref([]);
const criterios = ref([]);
const impactos = ref([]);
const sistemasExternos = ref([]);
const referenciasExternas = ref([]);
const referenciasEliminar = ref([]);
const entradaId = ref(null);
const entradaPublicada = ref(false);
const saving = ref(false);
const saveError = ref('');
const saveSuccess = ref('');
const uploadingImage = ref(false);
const uploadError = ref('');
const uploadingFile = ref(false);
const uploadFileError = ref('');
const archivosAdjuntos = ref([]);
const adjuntosEliminar = ref([]);
const adjuntosMeta = ref([]);
const loadingEntrada = ref(!!route.params.id);
const referenciaForm = reactive({
  sistema_id: '',
  externo_id: '',
  patron_regex: '',
  error: '',
});
const ubicaciones = ref([]);
const ubicacionesTotalEncontradas = ref(0);
const ubicacionDetalleNivelesCatalogo = ref([]);
const equipos = ref([]);
const loadingUbicaciones = ref(false);
const loadingEquipos = ref(false);
const nivelesUbicacion = reactive({
  nivel_1: [],
  nivel_2: [],
  nivel_3: [],
});
const syncingUbicacionFiltros = ref(false);
const ubicacionFiltros = reactive({
  nivel_1: '',
  nivel_2: '',
  nivel_3: '',
  q: '',
});
const syncingEquipoFiltros = ref(false);
const syncingUbicacionDesdeEquipo = ref(false);
const equipoFiltros = reactive({
  q: '',
});
const equiposSeleccionadosCache = ref([]);
const inventarioElementos = ref([]);
const inventarioQuery = ref('');
const inventarioTipoFiltro = ref('');
const inventarioPage = ref(1);
const inventarioPerPage = ref(20);
const ubicacionModo = ref('sap');
const UBICACIONES_SELECT_LIMIT = 500;
const EQUIPOS_SELECT_LIMIT = 500;

const normalizeDetalleNivelCodigo = (value) => String(value || '').trim().toUpperCase();
const normalizeDetalleNivelRama = (value) => {
  const normalized = String(value || '').trim().toUpperCase();
  return normalized || '--';
};

const ubicacionDetalleNivelIndex = computed(() => {
  const index = new Map();
  for (const item of ubicacionDetalleNivelesCatalogo.value) {
    const nivel = Number(item?.nivel || 0);
    const codigo = normalizeDetalleNivelCodigo(item?.codigo);
    if (!nivel || !codigo) continue;

    const rama = nivel <= 3
      ? '--'
      : normalizeDetalleNivelRama(item?.rama_nivel_3);
    index.set(`${nivel}|${rama}|${codigo}`, item);
  }
  return index;
});

const resolveDetalleNivelUbicacion = (nivel, codigo, ramaNivel3 = '--') => {
  const nivelNum = Number(nivel || 0);
  const codigoNorm = normalizeDetalleNivelCodigo(codigo);
  if (!nivelNum || !codigoNorm) return null;

  const rama = nivelNum <= 3
    ? '--'
    : normalizeDetalleNivelRama(ramaNivel3);

  return ubicacionDetalleNivelIndex.value.get(`${nivelNum}|${rama}|${codigoNorm}`)
    || ubicacionDetalleNivelIndex.value.get(`${nivelNum}|--|${codigoNorm}`)
    || null;
};

const isConfigTruthy = (value) => {
  if (typeof value === 'boolean') return value;
  if (typeof value === 'number') return value === 1;
  const normalized = String(value ?? '').trim().toLowerCase();
  return ['1', 'true', 'yes', 'si', 'sí'].includes(normalized);
};

const archivoMaxMb = computed(() => {
  const raw = Number(config.archivos_max_mb ?? 5);
  if (!Number.isFinite(raw) || raw <= 0) return 5;
  return Math.min(raw, 10);
});

const archivosPermitidos = computed(() => {
  const permisos = config.archivos_permitidos || {};
  return Object.keys(permisos).filter((ext) => isConfigTruthy(permisos[ext]));
});

const archivoAccept = computed(() => archivosPermitidos.value.map((ext) => `.${ext}`).join(','));

const archivosPermitidosLabel = computed(() => {
  if (!archivosPermitidos.value.length) return 'Ninguno';
  return archivosPermitidos.value.map((ext) => ext.toUpperCase()).join(', ');
});

const formatUbicacionLabel = (ubicacion) => {
  const codigo = String(ubicacion?.codigo || '').trim();
  const nombre = String(ubicacion?.nombre || '').trim();
  if (codigo && nombre) return `${codigo} - ${nombre}`;
  return codigo || nombre || 'Ubicación sin etiqueta';
};

const initEditor = () => {
  if (editor.value) return;
  editor.value = new Editor({
    extensions: [
      StarterKit,
      Image,
    ],
    content: form.cuerpo_html || '',
    onUpdate: ({ editor: editorInstance }) => {
      form.cuerpo_html = editorInstance.getHTML();
      form.cuerpo_texto = editorInstance.getText();
    },
  });
  onInput();
};

const exec = (command) => {
  if (!editor.value) return;
  const chain = editor.value.chain().focus();
  switch (command) {
    case 'bold':
      chain.toggleBold().run();
      break;
    case 'italic':
      chain.toggleItalic().run();
      break;
    case 'insertUnorderedList':
      if (typeof chain.toggleBulletList === 'function') {
        chain.toggleBulletList().run();
      } else {
        chain.toggleList('bulletList', 'listItem').run();
      }
      break;
    case 'insertOrderedList':
      if (typeof chain.toggleOrderedList === 'function') {
        chain.toggleOrderedList().run();
      } else {
        chain.toggleList('orderedList', 'listItem').run();
      }
      break;
    default:
      break;
  }
  onInput();
};

const onInput = () => {
  if (!editor.value) return;
  form.cuerpo_html = editor.value.getHTML();
  form.cuerpo_texto = editor.value.getText();
};

const parseAdjuntoId = (value) => {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
};

const resolveAdjuntoUrl = (adjunto) => {
  if (!adjunto) return null;
  if (adjunto.url) return adjunto.url;
  if (!adjunto.ruta) return null;
  const ruta = String(adjunto.ruta).replace(/^\/+/, '');
  return ruta.startsWith('storage/') ? `/${ruta}` : `/storage/${ruta}`;
};

const normalizePathFromUrl = (raw) => {
  const value = String(raw || '').trim();
  if (!value) return '';
  try {
    return new URL(value, window.location.origin).pathname.replace(/\/+$/, '');
  } catch {
    return value.split('?')[0].split('#')[0].replace(/\/+$/, '');
  }
};

const isImagenAdjunto = (adjunto) => {
  const tipo = String(adjunto?.tipo || '').toLowerCase();
  if (tipo) return tipo === 'imagen';
  const mime = String(adjunto?.mime_final || adjunto?.mime_original || '').toLowerCase();
  return mime.startsWith('image/');
};

const upsertAdjuntoMeta = (adjunto) => {
  if (!adjunto?.id) return;
  const id = String(adjunto.id);
  const index = adjuntosMeta.value.findIndex((item) => String(item.id) === id);
  if (index === -1) {
    adjuntosMeta.value = [...adjuntosMeta.value, adjunto];
    return;
  }
  const next = [...adjuntosMeta.value];
  next[index] = { ...next[index], ...adjunto };
  adjuntosMeta.value = next;
};

const removeAdjuntoMeta = (id) => {
  if (!id) return;
  adjuntosMeta.value = adjuntosMeta.value.filter((item) => String(item.id) !== String(id));
};

const adjuntoMetaById = computed(() =>
  new Map(adjuntosMeta.value.map((item) => [String(item.id), item]))
);

const extractImageRefsFromHtml = (html) => {
  if (!html) return { ids: new Set(), srcPaths: new Set() };
  const doc = new DOMParser().parseFromString(String(html), 'text/html');
  const ids = new Set();
  const srcPaths = new Set();
  doc.querySelectorAll('img').forEach((img) => {
    const attrId = img.getAttribute('title') || img.getAttribute('data-adjunto-id');
    const parsedId = parseAdjuntoId(attrId);
    if (parsedId) ids.add(parsedId);
    const src = img.getAttribute('src');
    const normalizedSrc = normalizePathFromUrl(src);
    if (normalizedSrc) srcPaths.add(normalizedSrc);
  });
  return { ids, srcPaths };
};

const syncImagenesAdjuntosConEditor = async () => {
  const { ids: htmlImageIds, srcPaths } = extractImageRefsFromHtml(form.cuerpo_html || '');
  const idsPresentes = new Set(htmlImageIds);

  for (const meta of adjuntosMeta.value) {
    if (!isImagenAdjunto(meta)) continue;
    const id = parseAdjuntoId(meta.id);
    if (!id) continue;
    const normalizedPath = normalizePathFromUrl(resolveAdjuntoUrl(meta));
    if (normalizedPath && srcPaths.has(normalizedPath)) {
      idsPresentes.add(id);
    }
  }

  idsPresentes.forEach((id) => {
    if (!form.adjuntos.some((adjId) => String(adjId) === String(id))) {
      form.adjuntos = [...form.adjuntos, id];
    }
  });

  const imagenesAsociadas = (form.adjuntos || [])
    .map((id) => parseAdjuntoId(id))
    .filter((id) => id !== null)
    .filter((id) => isImagenAdjunto(adjuntoMetaById.value.get(String(id))));

  const removidas = imagenesAsociadas.filter((id) => !idsPresentes.has(id));
  if (!removidas.length) return { removidas: 0, temporalesNoEliminadas: 0 };

  let temporalesNoEliminadas = 0;
  for (const id of removidas) {
    const meta = adjuntoMetaById.value.get(String(id));
    const isTemporal = !meta?.entrada_id;

    if (isTemporal) {
      try {
        // Imagen temporal: eliminar inmediatamente para no dejar archivo huérfano.
        // eslint-disable-next-line no-await-in-loop
        await window.axios.delete(`/api/v1/adjuntos/${id}`);
      } catch {
        temporalesNoEliminadas += 1;
      }
    } else if (!adjuntosEliminar.value.includes(id)) {
      adjuntosEliminar.value = [...adjuntosEliminar.value, id];
    }

    form.adjuntos = form.adjuntos.filter((adjId) => String(adjId) !== String(id));
    removeAdjuntoMeta(id);
  }

  return { removidas: removidas.length, temporalesNoEliminadas };
};

const handlePaste = async (event) => {
  const items = event.clipboardData?.items || [];
  const imageItems = Array.from(items).filter((item) => item.type.startsWith('image/'));
  if (!imageItems.length) return;

  event.preventDefault();
  for (const item of imageItems) {
    const file = item.getAsFile();
    if (file) {
      // eslint-disable-next-line no-await-in-loop
      await uploadImage(file);
    }
  }
};

const loadConfig = async () => {
  const items = await fetchConfiguracionSistema();
  const map = Object.fromEntries(items.map((i) => [i.clave, i.valor]));
  config.max_adjuntos_por_entrada = map['max_adjuntos_por_entrada'];
  config.imagenes_max_mb = map['imagenes.max_mb'];
  config.archivos_max_mb = map['archivos.max_mb'];
  config.archivos_permitidos = {
    pdf: isConfigTruthy(map['archivos.permitir_pdf'] ?? '1'),
    doc: isConfigTruthy(map['archivos.permitir_doc'] ?? '1'),
    docx: isConfigTruthy(map['archivos.permitir_docx'] ?? '1'),
    xls: isConfigTruthy(map['archivos.permitir_xls'] ?? '1'),
    xlsx: isConfigTruthy(map['archivos.permitir_xlsx'] ?? '1'),
  };
  const per = Number(map['paginacion.default_per_page'] ?? 20);
  inventarioPerPage.value = Number.isFinite(per) && per > 0 ? per : 20;
};

const loadPmData = async () => {
  const [ordenesRes, actividadesRes, matrizRes] = await Promise.all([
    window.axios.get('/api/v1/catalogos/pm/ordenes'),
    window.axios.get('/api/v1/catalogos/pm/actividades'),
    window.axios.get('/api/v1/catalogos/pm/matriz'),
  ]);

  pmOrdenes.value = ordenesRes.data?.data ?? ordenesRes.data;
  pmActividades.value = actividadesRes.data?.data ?? actividadesRes.data;
  pmMatriz.value = matrizRes.data?.data ?? matrizRes.data;
};

const loadCatalogos = async () => {
  const [criteriosRes, impactosRes] = await Promise.all([
    window.axios.get('/api/v1/catalogos/criterios'),
    window.axios.get('/api/v1/catalogos/impactos'),
  ]);
  criterios.value = criteriosRes.data?.data ?? criteriosRes.data;
  impactos.value = impactosRes.data?.data ?? impactosRes.data;
};

const loadSistemasExternos = async () => {
  const response = await window.axios.get('/api/v1/sistemas-externos');
  sistemasExternos.value = response.data?.data ?? response.data;
};

const loadUbicacionDetalleNivelesCatalogo = async () => {
  try {
    const response = await window.axios.get('/api/v1/ubicaciones/detalle-niveles');
    ubicacionDetalleNivelesCatalogo.value = response.data?.data ?? response.data ?? [];
  } catch {
    ubicacionDetalleNivelesCatalogo.value = [];
  }
};

const loadNivel1Ubicaciones = async () => {
  const response = await window.axios.get('/api/v1/ubicaciones', {
    params: { distinct_nivel: 1 },
  });
  nivelesUbicacion.nivel_1 = response.data?.data ?? [];
};

const loadNivel2Ubicaciones = async () => {
  if (!ubicacionFiltros.nivel_1) {
    nivelesUbicacion.nivel_2 = [];
    return;
  }

  const response = await window.axios.get('/api/v1/ubicaciones', {
    params: {
      distinct_nivel: 2,
      nivel_1: ubicacionFiltros.nivel_1,
    },
  });
  nivelesUbicacion.nivel_2 = response.data?.data ?? [];
};

const loadNivel3Ubicaciones = async () => {
  if (!ubicacionFiltros.nivel_1 || !ubicacionFiltros.nivel_2) {
    nivelesUbicacion.nivel_3 = [];
    return;
  }

  const response = await window.axios.get('/api/v1/ubicaciones', {
    params: {
      distinct_nivel: 3,
      nivel_1: ubicacionFiltros.nivel_1,
      nivel_2: ubicacionFiltros.nivel_2,
    },
  });
  nivelesUbicacion.nivel_3 = response.data?.data ?? [];
};

const loadUbicaciones = async () => {
  if (!ubicacionFiltros.nivel_1) {
    ubicaciones.value = [];
    ubicacionesTotalEncontradas.value = 0;
    return;
  }

  loadingUbicaciones.value = true;
  try {
    const response = await window.axios.get('/api/v1/ubicaciones', {
      params: {
        nivel_1: ubicacionFiltros.nivel_1,
        nivel_2: ubicacionFiltros.nivel_2 || undefined,
        nivel_3: ubicacionFiltros.nivel_3 || undefined,
        q: ubicacionFiltros.q || undefined,
        limit: UBICACIONES_SELECT_LIMIT,
      },
    });
    ubicaciones.value = response.data?.data ?? response.data ?? [];
    const total = Number(response.data?.meta?.total ?? ubicaciones.value.length);
    ubicacionesTotalEncontradas.value = Number.isFinite(total) ? total : ubicaciones.value.length;
  } finally {
    loadingUbicaciones.value = false;
  }
};

const loadEquipos = async () => {
  const hasUbicacion = Boolean(form.ubicacion_tecnica_id);
  const hasQuery = Boolean(equipoFiltros.q && equipoFiltros.q.trim() !== '');

  if (!hasUbicacion && !hasQuery) {
    equipos.value = [];
    return;
  }

  loadingEquipos.value = true;
  try {
    const response = await window.axios.get('/api/v1/equipos', {
      params: {
        ubicacion_tecnica_id: hasUbicacion ? form.ubicacion_tecnica_id : undefined,
        q: equipoFiltros.q || undefined,
        limit: EQUIPOS_SELECT_LIMIT,
      },
    });
    equipos.value = response.data?.data ?? response.data ?? [];
  } finally {
    loadingEquipos.value = false;
  }
};

const loadUbicacionById = async (id) => {
  if (!id) return null;
  const response = await window.axios.get('/api/v1/ubicaciones', {
    params: { id, limit: 1 },
  });
  const rows = response.data?.data ?? response.data ?? [];
  return rows[0] || null;
};

const syncUbicacionFiltersFromSelected = async (id) => {
  if (!id) return;

  let ubicacion = ubicaciones.value.find((item) => String(item.id) === String(id)) || null;
  if (!ubicacion) {
    ubicacion = await loadUbicacionById(id);
  }
  if (!ubicacion) return;

  syncingUbicacionFiltros.value = true;
  try {
    if (ubicacion.nivel_1) {
      ubicacionFiltros.nivel_1 = ubicacion.nivel_1;
    }
    await loadNivel2Ubicaciones();

    if (ubicacion.nivel_2) {
      ubicacionFiltros.nivel_2 = ubicacion.nivel_2;
    }
    ubicacionFiltros.nivel_3 = '';
    await loadNivel3Ubicaciones();
    if (ubicacion.nivel_3) {
      ubicacionFiltros.nivel_3 = ubicacion.nivel_3;
    }
    await loadUbicaciones();

    if (!ubicaciones.value.some((item) => String(item.id) === String(ubicacion.id))) {
      ubicaciones.value.unshift(ubicacion);
    }
  } finally {
    syncingUbicacionFiltros.value = false;
  }
};

const clearUbicacionFiltros = () => {
  ubicacionFiltros.nivel_1 = '';
  ubicacionFiltros.nivel_2 = '';
  ubicacionFiltros.nivel_3 = '';
  ubicacionFiltros.q = '';
  syncingEquipoFiltros.value = true;
  equipoFiltros.q = '';
  syncingEquipoFiltros.value = false;
  form.ubicacion_tecnica_id = '';
  form.equipo_id = '';
  equipos.value = [];
  ubicaciones.value = [];
  ubicacionesTotalEncontradas.value = 0;
  nivelesUbicacion.nivel_2 = [];
  nivelesUbicacion.nivel_3 = [];
};

const loadInventario = async () => {
  const response = await window.axios.get('/api/v1/inventario/elementos');
  inventarioElementos.value = response.data?.data ?? response.data;
};

const pmOrdenesActivas = computed(() => pmOrdenes.value.filter((orden) => orden.activo));
const pmActividadesActivas = computed(() => pmActividades.value.filter((act) => act.activo));
const pmMatrizActiva = computed(() =>
  pmMatriz.value.filter((row) => row.activo)
);
const sistemasExternosActivos = computed(() => sistemasExternos.value.filter((s) => s.activo));
const referenciaSistemaNombre = computed(() => {
  const sistema = sistemasExternos.value.find((s) => String(s.id) === String(referenciaForm.sistema_id));
  return sistema?.nombre || 'Sistema';
});

const ubicacionesFlat = computed(() =>
  ubicaciones.value.map((item) => ({
    ...item,
    label: formatUbicacionLabel(item),
  }))
);

const equiposFiltrados = computed(() => equipos.value);

const inventarioFiltradoTexto = computed(() => {
  const q = inventarioQuery.value.trim().toLowerCase();
  if (!q) return inventarioElementos.value;
  return inventarioElementos.value.filter((item) => {
    return (
      String(item.codigo || '').toLowerCase().includes(q) ||
      String(item.nombre || '').toLowerCase().includes(q) ||
      String(item.tipo || '').toLowerCase().includes(q) ||
      String(item.red?.nombre || '').toLowerCase().includes(q)
    );
  });
});

const inventarioTiposFiltro = [
  { value: 'nodo', label: 'Nodo' },
  { value: 'enlace', label: 'Enlace' },
  { value: 'tunel', label: 'Túnel' },
  { value: 'servicio', label: 'Servicio' },
];

const inventarioFiltrado = computed(() => {
  if (!inventarioTipoFiltro.value) return inventarioFiltradoTexto.value;
  return inventarioFiltradoTexto.value.filter(
    (item) => String(item.tipo || '').toLowerCase() === inventarioTipoFiltro.value
  );
});

const inventarioTotalPages = computed(() =>
  Math.max(1, Math.ceil(inventarioFiltrado.value.length / inventarioPerPage.value))
);

const inventarioPaginado = computed(() => {
  const start = (inventarioPage.value - 1) * inventarioPerPage.value;
  return inventarioFiltrado.value.slice(start, start + inventarioPerPage.value);
});

const inventarioRange = computed(() => {
  if (!inventarioFiltrado.value.length) return { start: 0, end: 0 };
  const start = (inventarioPage.value - 1) * inventarioPerPage.value + 1;
  const end = Math.min(inventarioPage.value * inventarioPerPage.value, inventarioFiltrado.value.length);
  return { start, end };
});

const inventarioSeleccionados = computed(() =>
  form.inv_elementos
    .map((id) => inventarioElementos.value.find((item) => String(item.id) === String(id)))
    .filter(Boolean)
);

const EXTENSION_SCHEMA_BY_TIPO = {
  nodo: {
    table: 'inv_detalle_nodos',
    fields: [
      { key: 'ne_id', label: 'NE ID' },
      { key: 'ne_dbid', label: 'NE DBID' },
      { key: 'dn_externo', label: 'DN Externo' },
      { key: 'native_name', label: 'Native Name' },
      { key: 'user_label', label: 'User Label' },
      { key: 'nombre_producto', label: 'Nombre Producto' },
      { key: 'tipo_equipo', label: 'Tipo Equipo' },
      { key: 'version_me', label: 'Versión ME' },
      { key: 'direccion_red', label: 'Dirección Red' },
      { key: 'nombre_grupo', label: 'Nombre Grupo' },
    ],
  },
  enlace: {
    table: 'inv_detalle_enlaces',
    fields: [
      { key: 'instancia_enlace_id', label: 'Instancia Enlace ID' },
      { key: 'motlink_label', label: 'Motlink Label' },
      { key: 'trail_id', label: 'Trail ID' },
      { key: 'nodo_a_ne_id', label: 'Nodo A NE ID' },
      { key: 'nodo_z_ne_id', label: 'Nodo Z NE ID' },
    ],
  },
  servicio: {
    table: 'inv_detalle_servicios',
    fields: [
      { key: 'instancia_servicio_id', label: 'Instancia Servicio ID' },
      { key: 'user_label', label: 'User Label' },
      { key: 'cliente', label: 'Cliente' },
      { key: 'tipo_servicio', label: 'Tipo Servicio' },
      { key: 'ethvpn_id', label: 'ETHVPN ID' },
    ],
  },
  tunel: {
    table: 'inv_detalle_tuneles',
    fields: [
      { key: 'instancia_tunel_id', label: 'Instancia Túnel ID' },
      { key: 'user_label', label: 'User Label' },
      { key: 'cliente', label: 'Cliente' },
      { key: 'tipo_tunel', label: 'Tipo Túnel' },
      { key: 'ethvpn_id', label: 'ETHVPN ID' },
    ],
  },
};

const extensionTableName = (tipo) => {
  const key = String(tipo || '').toLowerCase();
  return EXTENSION_SCHEMA_BY_TIPO[key]?.table || null;
};

const hasExtensionTable = (tipo) => Boolean(extensionTableName(tipo));

const extensionDetalleByTipo = (elemento) => {
  const tipo = String(elemento?.tipo || '').toLowerCase();

  switch (tipo) {
    case 'nodo':
      return elemento?.detalle_nodo || null;
    case 'enlace':
      return elemento?.detalle_enlace || null;
    case 'servicio':
      return elemento?.detalle_servicio || null;
    case 'tunel':
      return elemento?.detalle_tunel || null;
    default:
      return null;
  }
};

const formatExtensionValue = (value) => {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return String(value);
};

const extensionTooltipRows = (elemento) => {
  const tipo = String(elemento?.tipo || '').toLowerCase();
  const schema = EXTENSION_SCHEMA_BY_TIPO[tipo];
  if (!schema) return [];

  const detalle = extensionDetalleByTipo(elemento) || {};
  return schema.fields
    .map((field) => ({
      key: field.key,
      label: field.label,
      value: formatExtensionValue(detalle[field.key]),
    }))
    .filter((field) => field.value !== '-');
};

const filteredActividades = computed(() => {
  if (!form.pm_clase_orden_id) return [];
  const ordenId = String(form.pm_clase_orden_id);
  const ordenActiva = pmOrdenes.value.find((o) => String(o.id) === ordenId)?.activo;
  if (!ordenActiva) return [];

  const allowedIds = new Set(
    pmMatrizActiva.value
      .filter((row) => String(row.pm_clase_orden_id) === ordenId)
      .map((row) => row.pm_clase_actividad_id)
  );
  return pmActividadesActivas.value.filter((act) => allowedIds.has(act.id));
});

const criterioSeleccionado = computed(() =>
  criterios.value.find((criterio) => String(criterio.id) === String(form.entrada_criterio_id))
);

const impactoSeleccionado = computed(() =>
  impactos.value.find((impacto) => String(impacto.id) === String(form.entrada_impacto_id))
);

const isSinImpacto = computed(() => {
  const impacto = impactoSeleccionado.value;
  if (!impacto) return false;
  const nombre = String(impacto.nombre || '').toLowerCase();
  return impacto.severidad === 0 || nombre.includes('sin impacto');
});

const eventoRequerido = computed(() => !!form.entrada_impacto_id && !isSinImpacto.value);

const pmOrdenSeleccionada = computed(() =>
  pmOrdenes.value.find((orden) => String(orden.id) === String(form.pm_clase_orden_id))
);

const pmActividadSeleccionada = computed(() =>
  pmActividades.value.find((actividad) => String(actividad.id) === String(form.pm_clase_actividad_id))
);

const ubicacionSeleccionada = computed(() =>
  ubicacionesFlat.value.find((ubicacion) => String(ubicacion.id) === String(form.ubicacion_tecnica_id))
);

const ensureEquipoSeleccionadoCache = (equipo) => {
  if (!equipo?.id) return;
  const id = String(equipo.id);
  const index = equiposSeleccionadosCache.value.findIndex((item) => String(item.id) === id);
  if (index === -1) {
    equiposSeleccionadosCache.value.push(equipo);
    return;
  }
  equiposSeleccionadosCache.value[index] = {
    ...equiposSeleccionadosCache.value[index],
    ...equipo,
  };
};

const equiposSeleccionados = computed(() =>
  (form.equipos || [])
    .map((id) => {
      const key = String(id);
      return (
        equiposSeleccionadosCache.value.find((item) => String(item.id) === key) ||
        equipos.value.find((item) => String(item.id) === key) ||
        { id, codigo: '', nombre: `Equipo #${id}` }
      );
    })
    .filter(Boolean)
);

const isEquipoSeleccionado = (id) =>
  (form.equipos || []).some((equipoId) => String(equipoId) === String(id));

const addEquipoSeleccionado = () => {
  if (!form.equipo_id) return;
  if (isEquipoSeleccionado(form.equipo_id)) {
    form.equipo_id = '';
    return;
  }

  const equipo = equipos.value.find((item) => String(item.id) === String(form.equipo_id));
  if (!equipo) return;

  form.equipos = [...(form.equipos || []), equipo.id];
  ensureEquipoSeleccionadoCache(equipo);
  form.equipo_id = '';
};

const removeEquipoSeleccionado = (id) => {
  form.equipos = (form.equipos || []).filter((equipoId) => String(equipoId) !== String(id));
  equiposSeleccionadosCache.value = equiposSeleccionadosCache.value.filter(
    (equipo) => String(equipo.id) !== String(id)
  );
};

const joinConY = (items) => {
  const clean = items.filter((item) => item && String(item).trim() !== '');
  if (!clean.length) return '';
  if (clean.length === 1) return clean[0];
  if (clean.length === 2) return `${clean[0]} y ${clean[1]}`;
  return `${clean.slice(0, -1).join(', ')} y ${clean[clean.length - 1]}`;
};

const formatFecha = (value) => {
  if (!value) return '';
  return String(value).replace('T', ' ');
};

const resumenTecnicoPreview = computed(() => {
  const lineas = [];
  let tipoRegistro = 'sin tipo registrado';
  if (form.tipo_registro === 'inventario') {
    tipoRegistro = 'de inventario';
  } else if (form.tipo_registro === 'operativo') {
    tipoRegistro = 'operativa';
  }
  const criterio = criterioSeleccionado.value?.nombre;
  const impacto = impactoSeleccionado.value?.nombre;

  const extras = [];
  extras.push(criterio ? `referente al criterio ${criterio}` : 'sin criterio técnico registrado');
  if (impacto) {
    extras.push(isSinImpacto.value ? 'sin nivel de impacto registrado' : `con nivel de impacto ${impacto}`);
  } else {
    extras.push('sin nivel de impacto registrado');
  }
  const base = `Entrada ${tipoRegistro}`;
  lineas.push(extras.length ? `${base}, ${joinConY(extras)}.` : `${base}.`);

  if (impacto && !isSinImpacto.value) {
    if (form.tipo_evento) {
      const tipoEvento = form.tipo_evento === 'ANOMALIA' ? 'ANOMALÍA' : 'FALLA';
      lineas.push(`Con ${tipoEvento} detectada.`);
    } else {
      lineas.push('Sin FALLA o ANOMALÍA detectada.');
    }
  }

  const inicio = formatFecha(form.fecha_inicio);
  const fin = formatFecha(form.fecha_fin);
  if (inicio && fin) {
    lineas.push(`Periodo operativo: desde ${inicio} hasta ${fin}.`);
  } else if (inicio) {
    lineas.push(`Inicio registrado: ${inicio}.`);
  } else if (fin) {
    lineas.push(`Fin registrado: ${fin}.`);
  }

  if (ubicacionModo.value === 'manual') {
    const partes = [];
    if (form.ubicacion_manual) partes.push(`Ubicación manual registrada: ${form.ubicacion_manual}`);
    if (form.equipo_manual) partes.push(`Equipo manual registrado: ${form.equipo_manual}`);
    if (partes.length) {
      lineas.push(`${partes.join('. ')}.`);
    }
  } else {
    const partes = [];
    if (ubicacionSeleccionada.value?.nombre) {
      partes.push(`Ubicación técnica: ${ubicacionSeleccionada.value.nombre}`);
    }
    if (equiposSeleccionados.value.length) {
      const nombresEquipos = equiposSeleccionados.value
        .map((equipo) => equipo.nombre || equipo.codigo)
        .filter(Boolean);
      const listaEquipos = joinConY(nombresEquipos);
      if (listaEquipos) {
        partes.push(`Equipos asociados: ${listaEquipos}`);
      }
    }
    if (partes.length) {
      lineas.push(`${partes.join('. ')}.`);
    }
  }

  if (pmOrdenSeleccionada.value || pmActividadSeleccionada.value) {
    const orden = pmOrdenSeleccionada.value?.nombre;
    const actividad = pmActividadSeleccionada.value?.nombre;
    let pmLine = `La entrada ${tipoRegistro}`;
    if (orden && actividad) {
      pmLine += ` es de ${orden} para ${actividad}.`;
    } else if (orden) {
      pmLine += ` es de ${orden}.`;
    } else if (actividad) {
      pmLine += ` es para ${actividad}.`;
    }
    lineas.push(pmLine);
  }

  if (form.tipo_registro === 'inventario' && form.accion_inventario) {
    lineas.push(`Se registra un movimiento de ${form.accion_inventario} en la red.`);
  }

  if (inventarioSeleccionados.value.length) {
    const nombres = inventarioSeleccionados.value.map((item) => item.nombre || item.codigo).filter(Boolean);
    const lista = joinConY(nombres);
    if (lista) {
      lineas.push(`Se involucran elementos: ${lista}.`);
    }
  } else if (form.tipo_registro === 'inventario') {
    lineas.push('Sin elementos asociados.');
  }

  if (referenciasExternas.value.length) {
    const segmentos = referenciasExternas.value.map((ref) => `${ref.sistema_nombre} (folio ${ref.externo_id})`);
    const lista = joinConY(segmentos);
    if (lista) {
      lineas.push(`Para esta actividad se registran las referencias externas: ${lista}.`);
    }
  }

  return lineas.length ? lineas.join('\n') : 'Resumen técnico pendiente de captura.';
});

const onOrdenChange = () => {
  form.pm_clase_actividad_id = '';
};

const onImpactoChange = () => {
  if (isSinImpacto.value) {
    form.tipo_evento = '';
  }
};

const removeReferenciaExterna = async (id) => {
  const index = referenciasExternas.value.findIndex((ref) => ref.id === id);
  if (index === -1) return;
  const refItem = referenciasExternas.value[index];
  if (refItem.backend_id && entradaId.value && !referenciasEliminar.value.includes(refItem.backend_id)) {
    referenciasEliminar.value = [...referenciasEliminar.value, refItem.backend_id];
  }
  referenciasExternas.value.splice(index, 1);
};

const onReferenciaSistemaChange = () => {
  const sistema = sistemasExternos.value.find((s) => String(s.id) === String(referenciaForm.sistema_id));
  referenciaForm.patron_regex = sistema?.patron_regex || '';
  referenciaForm.error = '';
  referenciaForm.externo_id = '';
};

const referenciaValida = computed(() => {
  if (!referenciaForm.sistema_id || !referenciaForm.externo_id) return false;
  if (!referenciaForm.patron_regex) return true;
  try {
    const regex = new RegExp(referenciaForm.patron_regex);
    return regex.test(referenciaForm.externo_id);
  } catch {
    return false;
  }
});

const onReferenciaFormInput = () => {
  if (!referenciaForm.externo_id) {
    referenciaForm.error = '';
    return;
  }
  if (!referenciaForm.patron_regex) {
    referenciaForm.error = '';
    return;
  }
  try {
    const regex = new RegExp(referenciaForm.patron_regex);
    referenciaForm.error = regex.test(referenciaForm.externo_id) ? '' : 'El identificador no cumple el patrón.';
  } catch {
    referenciaForm.error = 'Patrón inválido.';
  }
};

const safeClientId = () => {
  const cryptoApi = typeof window !== 'undefined' ? window.crypto : null;
  if (cryptoApi && typeof cryptoApi.randomUUID === 'function') {
    return cryptoApi.randomUUID();
  }
  return `tmp-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`;
};

const addReferenciaExterna = () => {
  if (!referenciaForm.sistema_id || !referenciaForm.externo_id) {
    referenciaForm.error = 'Completa sistema y folio.';
    return;
  }
  if (!referenciaValida.value) {
    referenciaForm.error = 'El identificador no cumple el patrón.';
    return;
  }
  const alreadyExists = referenciasExternas.value.some(
    (ref) =>
      String(ref.sistema_id) === String(referenciaForm.sistema_id) &&
      String(ref.externo_id) === String(referenciaForm.externo_id)
  );
  if (alreadyExists) {
    referenciaForm.error = 'La referencia ya fue agregada.';
    return;
  }
  const sistema = sistemasExternos.value.find((s) => String(s.id) === String(referenciaForm.sistema_id));
  const nuevo = {
    id: safeClientId(),
    backend_id: null,
    sistema_id: referenciaForm.sistema_id,
    sistema_nombre: sistema?.nombre || 'Sistema',
    externo_id: referenciaForm.externo_id,
    patron_regex: referenciaForm.patron_regex,
    error: '',
  };
  referenciasExternas.value.push(nuevo);
  if (entradaId.value) {
    persistReferencia(nuevo).catch(() => {
      saveError.value = 'No se pudo registrar la referencia externa.';
    });
  }
  referenciaForm.sistema_id = '';
  referenciaForm.externo_id = '';
  referenciaForm.patron_regex = '';
  referenciaForm.error = '';
};

const demoPlaceholder = (ref) => {
  if (!ref.patron_regex) return '';
  const pattern = String(ref.patron_regex);
  if (pattern.includes('\\d{8}') && pattern.includes('\\d{4}')) {
    return '00000000-0000';
  }
  if (pattern.includes('\\d{1,12}') || pattern.includes('\\d{1, 12}')) {
    return '0';
  }
  return pattern;
};

const isInventarioSelected = (id) => form.inv_elementos.some((itemId) => String(itemId) === String(id));

const toggleInventario = (elemento) => {
  if (isInventarioSelected(elemento.id)) {
    form.inv_elementos = form.inv_elementos.filter((itemId) => String(itemId) !== String(elemento.id));
  } else {
    form.inv_elementos = [...form.inv_elementos, elemento.id];
  }
};

const openImageDialog = () => {
  if (uploadingImage.value) return;
  imageInput.value?.click();
};

const openFileDialog = () => {
  if (uploadingFile.value || archivosPermitidos.value.length === 0) return;
  fileInput.value?.click();
};

const onImageSelected = async (event) => {
  const files = Array.from(event.target.files || []);
  if (!files.length) return;
  for (const file of files) {
    // eslint-disable-next-line no-await-in-loop
    await uploadImage(file);
  }
  event.target.value = '';
};

const onFileSelected = async (event) => {
  const files = Array.from(event.target.files || []);
  if (!files.length) return;
  for (const file of files) {
    // eslint-disable-next-line no-await-in-loop
    await uploadFile(file);
  }
  event.target.value = '';
};

const uploadImage = async (file) => {
  uploadError.value = '';
  const maxAdjuntos = Number(config.max_adjuntos_por_entrada || 15);
  if (form.adjuntos.length >= maxAdjuntos) {
    uploadError.value = 'Se alcanzó el límite de adjuntos permitidos.';
    return;
  }

  try {
    uploadingImage.value = true;
    await window.axios.get('/sanctum/csrf-cookie');
    const formData = new FormData();
    formData.append('file', file);
    formData.append('tipo', 'imagen');
    const response = await window.axios.post('/api/v1/adjuntos/imagen', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const adjunto = response.data?.data ?? response.data;
    if (adjunto?.id) {
      form.adjuntos = [...form.adjuntos, adjunto.id];
      upsertAdjuntoMeta(adjunto);
    }
    if (adjunto?.url) {
      insertImage(adjunto.url, adjunto.id);
    }
  } catch (error) {
    uploadError.value = error.response?.data?.message || 'No se pudo subir la imagen.';
  } finally {
    uploadingImage.value = false;
  }
};

const normalizeExtension = (name) => {
  const parts = String(name || '').split('.');
  if (parts.length < 2) return '';
  return parts.pop().toLowerCase();
};

const uploadFile = async (file) => {
  uploadFileError.value = '';
  const maxAdjuntos = Number(config.max_adjuntos_por_entrada || 15);
  if (form.adjuntos.length >= maxAdjuntos) {
    uploadFileError.value = 'Se alcanzó el límite de adjuntos permitidos.';
    return;
  }

  if (!archivosPermitidos.value.length) {
    uploadFileError.value = 'No hay tipos de archivo habilitados para subir.';
    return;
  }

  const ext = normalizeExtension(file.name);
  if (!ext || !archivosPermitidos.value.includes(ext)) {
    uploadFileError.value = `Tipo de archivo no permitido. Permitidos: ${archivosPermitidosLabel.value}.`;
    return;
  }

  const maxBytes = archivoMaxMb.value * 1024 * 1024;
  if (file.size > maxBytes) {
    uploadFileError.value = `El archivo supera el máximo permitido (${archivoMaxMb.value} MB).`;
    return;
  }

  try {
    uploadingFile.value = true;
    await window.axios.get('/sanctum/csrf-cookie');
    const formData = new FormData();
    formData.append('file', file);
    const response = await window.axios.post('/api/v1/adjuntos/archivo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const adjunto = response.data?.data ?? response.data;
    if (adjunto?.id && !form.adjuntos.includes(adjunto.id)) {
      form.adjuntos = [...form.adjuntos, adjunto.id];
    }
    if (adjunto?.id) {
      archivosAdjuntos.value = [...archivosAdjuntos.value, adjunto];
      upsertAdjuntoMeta(adjunto);
    }
  } catch (error) {
    uploadFileError.value = error.response?.data?.message || 'No se pudo subir el archivo.';
  } finally {
    uploadingFile.value = false;
  }
};

const removeArchivoAdjunto = async (file) => {
  const id = typeof file === 'object' ? file.id : file;
  if (!id) return;
  const isTemporal = !file?.entrada_id;
  if (isTemporal) {
    try {
      await window.axios.delete(`/api/v1/adjuntos/${id}`);
    } catch (error) {
      uploadFileError.value = error.response?.data?.message || 'No se pudo eliminar el archivo adjunto.';
      return;
    }
  } else if (!adjuntosEliminar.value.includes(id)) {
    adjuntosEliminar.value = [...adjuntosEliminar.value, id];
  }
  archivosAdjuntos.value = archivosAdjuntos.value.filter((item) => String(item.id) !== String(id));
  form.adjuntos = form.adjuntos.filter((adjId) => String(adjId) !== String(id));
  removeAdjuntoMeta(id);
};

const insertImage = (url, adjuntoId) => {
  if (!editor.value) return;
  const imageAttrs = {
    src: url,
    alt: 'Imagen adjunta',
  };
  if (adjuntoId) imageAttrs.title = String(adjuntoId);
  editor.value.chain().focus().setImage(imageAttrs).run();
  onInput();
};

const formatDatetimeLocal = (value) => {
  if (!value) return '';
  const date = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const loadEntrada = async (id) => {
  if (!id) return;
  loadingEntrada.value = true;
  saveError.value = '';
  try {
    const response = await window.axios.get(`/api/v1/entradas/${id}`);
    const entrada = response.data?.data ?? response.data;

    entradaId.value = entrada.id;
    entradaPublicada.value = Boolean(entrada.publicado);
    form.titulo = entrada.titulo || '';
    form.cuerpo_html = entrada.cuerpo_html || '';
    form.cuerpo_texto = entrada.cuerpo_texto || '';
    form.fecha_inicio = formatDatetimeLocal(entrada.fecha_inicio);
    form.fecha_fin = formatDatetimeLocal(entrada.fecha_fin);
    form.pm_clase_orden_id = entrada.pm_clase_orden?.id || entrada.pm_clase_orden_id || '';
    form.pm_clase_actividad_id = entrada.pm_clase_actividad?.id || entrada.pm_clase_actividad_id || '';
    form.entrada_criterio_id = entrada.criterio?.id || entrada.entrada_criterio_id || '';
    form.entrada_impacto_id = entrada.impacto?.id || entrada.entrada_impacto_id || '';
    form.tipo_registro = entrada.tipo_registro || '';
    form.accion_inventario = entrada.accion_inventario || '';
    form.ubicacion_tecnica_id = entrada.ubicacion_tecnica_id || '';
    form.equipo_id = '';
    form.equipos = [];
    form.ubicacion_manual = entrada.ubicacion_manual || '';
    form.equipo_manual = entrada.equipo_manual || '';

    form.inv_elementos = (entrada.inventario_elementos || []).map((item) => item.id);
    const equiposRelacion = entrada.equipos || [];
    const equiposIds = equiposRelacion.length
      ? equiposRelacion.map((item) => item.id)
      : (entrada.equipo_id ? [entrada.equipo_id] : []);
    form.equipos = Array.from(new Set(equiposIds.map((id) => Number(id)).filter((id) => Number.isFinite(id))));
    equiposSeleccionadosCache.value = [...equiposRelacion];
    form.adjuntos = (entrada.adjuntos || []).map((item) => item.id);
    adjuntosMeta.value = [...(entrada.adjuntos || [])];
    archivosAdjuntos.value = (entrada.adjuntos || []).filter((item) => item.tipo === 'archivo');
    adjuntosEliminar.value = [];

    if (entrada.evento_detectado?.tipo_evento) {
      form.tipo_evento = entrada.evento_detectado.tipo_evento;
    }

    if (form.ubicacion_manual || form.equipo_manual) {
      ubicacionModo.value = 'manual';
    } else {
      ubicacionModo.value = 'sap';
    }

    if (ubicacionModo.value === 'sap' && form.ubicacion_tecnica_id) {
      await syncUbicacionFiltersFromSelected(form.ubicacion_tecnica_id);
      await loadEquipos();
    } else {
      equipos.value = [];
    }

    referenciasExternas.value = (entrada.referencias_externas || []).map((ref) => {
      const sistema = sistemasExternos.value.find((s) => String(s.id) === String(ref.sistema_externo_id));
      return {
        id: ref.id,
        backend_id: ref.id,
        sistema_id: ref.sistema_externo_id,
        sistema_nombre: sistema?.nombre || 'Sistema',
        externo_id: ref.externo_id,
        patron_regex: sistema?.patron_regex || '',
        error: '',
      };
    });
    referenciasEliminar.value = [];

    await nextTick();
    if (editor.value) {
      editor.value.commands.setContent(form.cuerpo_html || '', false);
      onInput();
    }
  } catch (error) {
    saveError.value = error.response?.data?.message || 'No se pudo cargar la entrada.';
  } finally {
    loadingEntrada.value = false;
  }
};

const resetForm = async () => {
  entradaId.value = null;
  entradaPublicada.value = false;
  form.titulo = '';
  form.fecha_inicio = '';
  form.fecha_fin = '';
  form.cuerpo_html = '';
  form.cuerpo_texto = '';
  form.pm_clase_orden_id = '';
  form.pm_clase_actividad_id = '';
  form.entrada_criterio_id = '';
  form.entrada_impacto_id = '';
  form.tipo_registro = '';
  form.accion_inventario = '';
  form.tipo_evento = '';
  form.ubicacion_tecnica_id = '';
  form.equipo_id = '';
  form.equipos = [];
  form.ubicacion_manual = '';
  form.equipo_manual = '';
  form.inv_elementos = [];
  form.adjuntos = [];
  adjuntosEliminar.value = [];
  archivosAdjuntos.value = [];
  adjuntosMeta.value = [];
  referenciasExternas.value = [];
  referenciasEliminar.value = [];
  referenciaForm.sistema_id = '';
  referenciaForm.externo_id = '';
  referenciaForm.patron_regex = '';
  referenciaForm.error = '';
  ubicacionFiltros.nivel_1 = '';
  ubicacionFiltros.nivel_2 = '';
  ubicacionFiltros.nivel_3 = '';
  ubicacionFiltros.q = '';
  syncingEquipoFiltros.value = true;
  equipoFiltros.q = '';
  syncingEquipoFiltros.value = false;
  nivelesUbicacion.nivel_2 = [];
  nivelesUbicacion.nivel_3 = [];
  ubicaciones.value = [];
  ubicacionesTotalEncontradas.value = 0;
  equipos.value = [];
  equiposSeleccionadosCache.value = [];
  ubicacionModo.value = 'sap';
  saveError.value = '';
  saveSuccess.value = '';
  uploadError.value = '';
  uploadFileError.value = '';
  inventarioPage.value = 1;
  await nextTick();
  if (editor.value) {
    editor.value.commands.setContent('', false);
    onInput();
  }
};

const normalizeId = (value) => {
  if (value === '' || value === null || value === undefined) return null;
  return Number(value);
};

const normalizeText = (value) => {
  const text = String(value ?? '').trim();
  return text.length ? text : null;
};

const validateEntrada = () => {
  const errors = [];
  if (!form.titulo.trim()) errors.push('El título es obligatorio.');
  if (!form.fecha_inicio) errors.push('La fecha de inicio es obligatoria.');
  if (!form.tipo_registro) errors.push('Selecciona el tipo de registro.');
  if (!form.cuerpo_texto || !form.cuerpo_texto.trim()) errors.push('La descripción es obligatoria.');
  if (eventoRequerido.value && !form.tipo_evento) errors.push('Selecciona FALLA o ANOMALÍA.');
  if ((form.pm_clase_orden_id && !form.pm_clase_actividad_id) || (!form.pm_clase_orden_id && form.pm_clase_actividad_id)) {
    errors.push('Selecciona clase de orden y clase de actividad.');
  }
  if (form.tipo_registro === 'inventario' && !form.accion_inventario) {
    errors.push('Selecciona la acción de inventario.');
  }
  return errors;
};

const buildPayload = () => {
  const equiposIds = ubicacionModo.value === 'sap'
    ? Array.from(new Set((form.equipos || []).map((id) => normalizeId(id)).filter((id) => id !== null)))
    : [];
  const inventarioIds = Array.from(new Set((form.inv_elementos || []).map((id) => normalizeId(id)).filter((id) => id !== null)));

  const payload = {
    titulo: form.titulo.trim(),
    cuerpo_html: form.cuerpo_html,
    cuerpo_texto: form.cuerpo_texto,
    fecha_inicio: form.fecha_inicio,
    fecha_fin: form.fecha_fin || null,
    entrada_criterio_id: normalizeId(form.entrada_criterio_id),
    entrada_impacto_id: normalizeId(form.entrada_impacto_id),
    pm_clase_orden_id: normalizeId(form.pm_clase_orden_id),
    pm_clase_actividad_id: normalizeId(form.pm_clase_actividad_id),
    tipo_registro: form.tipo_registro,
    accion_inventario: form.tipo_registro === 'inventario' ? form.accion_inventario || null : null,
    ubicacion_tecnica_id: ubicacionModo.value === 'sap' ? normalizeId(form.ubicacion_tecnica_id) : null,
    equipo_id: equiposIds[0] ?? null,
    ubicacion_manual: ubicacionModo.value === 'manual' ? normalizeText(form.ubicacion_manual) : null,
    equipo_manual: ubicacionModo.value === 'manual' ? normalizeText(form.equipo_manual) : null,
    equipos: equiposIds.map((id) => ({ id })),
    inventario_elementos: inventarioIds.map((id) => ({ id })),
  };

  if (form.adjuntos.length) {
    payload.adjuntos = [...form.adjuntos];
  }
  if (adjuntosEliminar.value.length) {
    payload.adjuntos_eliminar = [...adjuntosEliminar.value];
  }
  if (referenciasEliminar.value.length) {
    payload.referencias_eliminar = [...referenciasEliminar.value];
  }

  if (eventoRequerido.value && form.tipo_evento) {
    payload.evento_detectado = {
      tipo_evento: form.tipo_evento,
    };
  }

  return payload;
};

const persistReferencia = async (ref) => {
  if (!entradaId.value || ref.backend_id) return;
  ref.error = '';
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post('/api/v1/referencias', {
      entrada_id: entradaId.value,
      sistema_externo_id: ref.sistema_id,
      externo_id: ref.externo_id,
    });
    const saved = response.data?.data ?? response.data;
    if (saved?.id) {
      ref.backend_id = saved.id;
    }
  } catch (error) {
    ref.error = error.response?.data?.message || 'No se pudo registrar la referencia externa.';
    throw error;
  }
};

const syncReferenciasPendientes = async () => {
  const pendientes = referenciasExternas.value.filter((ref) => !ref.backend_id);
  if (!pendientes.length) return true;
  const results = await Promise.allSettled(pendientes.map((ref) => persistReferencia(ref)));
  return results.every((res) => res.status === 'fulfilled');
};

const guardarEntrada = async (publicar) => {
  if (saving.value) return;
  saveError.value = '';
  saveSuccess.value = '';
  onInput();

  const errors = validateEntrada();
  if (errors.length) {
    saveError.value = errors[0];
    return;
  }

  saving.value = true;
  let syncAdjuntosResult = { temporalesNoEliminadas: 0 };
  try {
    syncAdjuntosResult = await syncImagenesAdjuntosConEditor();
    await window.axios.get('/sanctum/csrf-cookie');
    const payload = buildPayload();
    let response;
    if (entradaId.value) {
      response = await window.axios.put(`/api/v1/entradas/${entradaId.value}`, payload);
    } else {
      response = await window.axios.post('/api/v1/entradas', payload);
    }
    const entrada = response.data?.data ?? response.data;
    if (!entradaId.value && entrada?.id) {
      entradaId.value = entrada.id;
    }
    entradaPublicada.value = Boolean(entrada?.publicado);
    // Limpia colas de eliminación ya aplicadas para evitar reenviar IDs inválidos en guardados posteriores.
    adjuntosEliminar.value = [];
    referenciasEliminar.value = [];

    const referenciasOk = await syncReferenciasPendientes();

    if (publicar && entradaId.value) {
      const publishResponse = await window.axios.post(`/api/v1/entradas/${entradaId.value}/publicar`);
      const entradaPublicadaResponse = publishResponse.data?.data ?? publishResponse.data;
      entradaPublicada.value = Boolean(entradaPublicadaResponse?.publicado);
      saveSuccess.value = 'Entrada publicada correctamente.';
      await router.push({ name: 'timeline' });
      return;
    }

    saveSuccess.value = isEditMode.value ? 'Entrada actualizada correctamente.' : 'Entrada guardada como borrador.';
    if (!referenciasOk) {
      saveError.value = 'Entrada guardada, pero algunas referencias externas no pudieron registrarse.';
    } else if (syncAdjuntosResult.temporalesNoEliminadas > 0) {
      saveError.value = 'Entrada guardada, pero algunas imágenes temporales no pudieron eliminarse en este momento.';
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const errorsBag = error.response?.data?.errors;
      if (errorsBag) {
        const flat = Object.values(errorsBag).flat();
        saveError.value = flat.join(' ');
      } else {
        saveError.value = error.response?.data?.message || 'No se pudo guardar la entrada.';
      }
    } else {
      saveError.value = error.response?.data?.message || 'No se pudo guardar la entrada.';
    }
  } finally {
    saving.value = false;
  }
};

const despublicarEntrada = async () => {
  if (!entradaId.value || !entradaPublicada.value || saving.value) return;

  const confirmed = window.confirm('Esta entrada volverá a estado borrador y dejará de mostrarse como publicada. ¿Deseas continuar?');
  if (!confirmed) return;

  try {
    saving.value = true;
    saveError.value = '';
    saveSuccess.value = '';
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post(`/api/v1/entradas/${entradaId.value}/despublicar`);
    const entrada = response.data?.data ?? response.data;
    entradaPublicada.value = Boolean(entrada?.publicado);
    saveSuccess.value = 'Entrada regresada a borrador correctamente.';
  } catch (error) {
    saveError.value = error.response?.data?.message || 'No se pudo cambiar la entrada a borrador.';
  } finally {
    saving.value = false;
  }
};

watch(ubicacionModo, (value) => {
  if (value === 'manual') {
    syncingEquipoFiltros.value = true;
    equipoFiltros.q = '';
    syncingEquipoFiltros.value = false;
    syncingUbicacionDesdeEquipo.value = false;
    form.ubicacion_tecnica_id = '';
    form.equipo_id = '';
    form.equipos = [];
    equipos.value = [];
    equiposSeleccionadosCache.value = [];
  } else {
    form.ubicacion_manual = '';
    form.equipo_manual = '';
  }
});

let ubicacionSearchTimer = null;
let equipoSearchTimer = null;

watch(
  () => ubicacionFiltros.nivel_1,
  async (value, previous) => {
    if (value === previous || syncingUbicacionFiltros.value) return;
    ubicacionFiltros.nivel_2 = '';
    ubicacionFiltros.nivel_3 = '';
    nivelesUbicacion.nivel_3 = [];
    form.ubicacion_tecnica_id = '';
    form.equipo_id = '';
    equipos.value = [];
    await loadNivel2Ubicaciones();
    await loadUbicaciones();
  }
);

watch(
  () => ubicacionFiltros.nivel_2,
  async (value, previous) => {
    if (value === previous || syncingUbicacionFiltros.value) return;
    ubicacionFiltros.nivel_3 = '';
    form.ubicacion_tecnica_id = '';
    form.equipo_id = '';
    equipos.value = [];
    await loadNivel3Ubicaciones();
    await loadUbicaciones();
  }
);

watch(
  () => ubicacionFiltros.nivel_3,
  async (value, previous) => {
    if (value === previous || syncingUbicacionFiltros.value) return;
    form.ubicacion_tecnica_id = '';
    form.equipo_id = '';
    equipos.value = [];
    await loadUbicaciones();
  }
);

watch(
  () => ubicacionFiltros.q,
  (value, previous) => {
    if (value === previous || syncingUbicacionFiltros.value) return;
    if (ubicacionSearchTimer) clearTimeout(ubicacionSearchTimer);
    ubicacionSearchTimer = setTimeout(() => {
      loadUbicaciones();
    }, 250);
  }
);

watch(
  () => form.ubicacion_tecnica_id,
  async (value, previous) => {
    if (value === previous || loadingEntrada.value) return;
    if (!syncingUbicacionDesdeEquipo.value) {
      form.equipo_id = '';
      syncingEquipoFiltros.value = true;
      equipoFiltros.q = '';
      syncingEquipoFiltros.value = false;
    }
    await loadEquipos();
  }
);

watch(
  () => equipoFiltros.q,
  (value, previous) => {
    if (value === previous || syncingEquipoFiltros.value) return;
    if (equipoSearchTimer) clearTimeout(equipoSearchTimer);
    equipoSearchTimer = setTimeout(() => {
      loadEquipos();
    }, 250);
  }
);

watch(
  () => form.equipo_id,
  async (value, previous) => {
    if (value === previous || loadingEntrada.value) return;
    if (!value) return;

    const equipo = equipos.value.find((item) => String(item.id) === String(value));
    const ubicacionId = equipo?.ubicacion_tecnica_id;
    if (!ubicacionId) return;

    if (String(form.ubicacion_tecnica_id) === String(ubicacionId)) {
      return;
    }

    syncingUbicacionDesdeEquipo.value = true;
    try {
      form.ubicacion_tecnica_id = String(ubicacionId);
      await syncUbicacionFiltersFromSelected(ubicacionId);
    } finally {
      syncingUbicacionDesdeEquipo.value = false;
    }
  }
);

watch(inventarioQuery, () => {
  inventarioPage.value = 1;
});

watch(inventarioTipoFiltro, () => {
  inventarioPage.value = 1;
});

watch(inventarioTotalPages, (value) => {
  if (inventarioPage.value > value) inventarioPage.value = value;
});

onMounted(async () => {
  initEditor();
  await loadConfig();
  await Promise.all([
    loadPmData(),
    loadCatalogos(),
    loadNivel1Ubicaciones(),
    loadInventario(),
    loadUbicacionDetalleNivelesCatalogo(),
  ]);
  await loadSistemasExternos();

  if (isEditMode.value) {
    await loadEntrada(route.params.id);
  }
});

watch(
  () => route.params.id,
  async (id) => {
    if (id) {
      loadingEntrada.value = true;
      await loadEntrada(id);
    } else {
      loadingEntrada.value = false;
      await resetForm();
    }
  }
);

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy();
    editor.value = null;
  }
});
</script>

<style scoped>
.entrada-editor :deep(.ProseMirror ul) {
  list-style: disc;
  padding-left: 1.5rem;
  margin: 0.25rem 0;
}

.entrada-editor :deep(.ProseMirror ol) {
  list-style: decimal;
  padding-left: 1.5rem;
  margin: 0.25rem 0;
}

.entrada-editor :deep(.ProseMirror li) {
  margin: 0.125rem 0;
}
</style>
