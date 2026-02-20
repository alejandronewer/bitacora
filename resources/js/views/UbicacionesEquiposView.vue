<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Ubicaciones y Equipos</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Ubicaciones Técnicas / Equipos</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Administra catálogo SAP-like, con niveles de ubicación y equipos asociados.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <input
            ref="importZpm017Input"
            type="file"
            class="hidden"
            accept=".xlsx,.xlsm"
            @change="onImportarZpm017File"
          />
          <button
            class="inline-flex items-center gap-2 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9] px-4 py-2.5 rounded-lg font-bold text-sm transition-all hover:bg-slate-50 dark:hover:bg-border-dark disabled:opacity-60 disabled:cursor-not-allowed"
            :disabled="importRunning"
            @click="onImportarZpm017"
          >
            <span class="material-symbols-outlined">upload_file</span>
            <span>{{ importRunning ? 'Importando ZPM017...' : 'Importar ZPM017' }}</span>
          </button>
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openUbicacionCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nueva ubicación</span>
          </button>
          <button
            class="inline-flex items-center gap-2 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9] px-4 py-2.5 rounded-lg font-bold text-sm transition-all hover:bg-slate-50 dark:hover:bg-border-dark"
            @click="openEquipoCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nuevo equipo</span>
          </button>
        </div>
      </div>

      <div class="space-y-6">
        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Ubicaciones técnicas</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">CRUD con paginación y filtros por código, nombre y niveles.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ ubicacionesTotalItems }} registros
            </span>
          </div>

          <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-4 gap-2">
            <input
              v-model="ubicacionesQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
              placeholder="Buscar ubicación..."
            />
            <select v-model="ubicacionesStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option value="">Todas</option>
              <option value="activo">Activas</option>
              <option value="inactivo">Inactivas</option>
            </select>
            <select v-model="ubicacionesUso" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option value="">Todos</option>
              <option value="usado">Usados</option>
              <option value="no_usado">No usados</option>
            </select>
            <button
              class="px-3 py-2 border border-slate-200 dark:border-border-dark rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-border-dark transition-colors"
              @click="clearUbicacionesFilters"
            >
              Limpiar filtros
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">Ubicación (código)</th>
                  <th class="px-6 py-4">Nombre</th>
                  <th class="px-6 py-4">Fuente</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="u in ubicaciones" :key="u.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">
                      <UbicacionCodigoTooltip
                        :ubicacion="u"
                        :resolve-detalle="resolveDetalleNivelUbicacion"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200">
                    {{ u.nombre }}
                  </td>
                  <td class="px-6 py-4 text-xs">
                    <div class="font-medium">{{ u.fuente || 'Manual' }}</div>
                    <div class="text-slate-500 dark:text-[#92adc9]">{{ formatDateTime(u.last_sync_at) }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="u.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ u.activo ? 'Activa' : 'Inactiva' }}
                    </span>
                    <span v-if="u.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openUbicacionEdit(u)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="u.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="u.in_use"
                        :title="u.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar ubicación'"
                        @click="deleteUbicacion(u)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!loadingUbicaciones && ubicaciones.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="5">Sin registros</td>
                </tr>
                <tr v-if="loadingUbicaciones">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="5">Cargando ubicaciones...</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="ubicacionesPage"
            :total-pages="ubicacionesTotalPages"
            :range-start="ubicacionesRange.start"
            :range-end="ubicacionesRange.end"
            :total-items="ubicacionesTotalItems"
            @update:page="fetchUbicaciones"
          />
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Equipos</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">CRUD con paginación y filtros por código, nombre, ubicación y área.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ equiposTotalItems }} registros
            </span>
          </div>

          <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-5 gap-2">
            <input
              v-model="equiposQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
              placeholder="Buscar equipo..."
            />
            <select v-model="equiposUbicacion" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option value="">Todas las ubicaciones</option>
              <option v-for="u in ubicacionesCatalogo" :key="u.id" :value="String(u.id)">
                {{ u.codigo }} - {{ u.nombre }}
              </option>
            </select>
            <select v-model="equiposStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option value="">Todos</option>
              <option value="activo">Activos</option>
              <option value="inactivo">Inactivos</option>
            </select>
            <select v-model="equiposUso" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option value="">Todos</option>
              <option value="usado">Usados</option>
              <option value="no_usado">No usados</option>
            </select>
            <button
              class="px-3 py-2 border border-slate-200 dark:border-border-dark rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-border-dark transition-colors"
              @click="clearEquiposFilters"
            >
              Limpiar filtros
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">Equipo</th>
                  <th class="px-6 py-4">Ubicación técnica</th>
                  <th class="px-6 py-4">Área</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="e in equipos" :key="e.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ e.nombre }}</div>
                    <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ e.codigo }}</div>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div>{{ e.ubicacion?.nombre || '-' }}</div>
                    <div v-if="e.ubicacion?.codigo" class="text-xs text-slate-500 dark:text-[#92adc9]">
                      <UbicacionCodigoTooltip
                        :ubicacion="e.ubicacion"
                        :resolve-detalle="resolveDetalleNivelUbicacion"
                      />
                    </div>
                    <div v-else class="text-xs text-slate-500 dark:text-[#92adc9]">-</div>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ e.area || '-' }}</td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="e.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ e.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <span v-if="e.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openEquipoEdit(e)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="e.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="e.in_use"
                        :title="e.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar equipo'"
                        @click="deleteEquipo(e)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!loadingEquipos && equipos.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="5">Sin registros</td>
                </tr>
                <tr v-if="loadingEquipos">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="5">Cargando equipos...</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="equiposPage"
            :total-pages="equiposTotalPages"
            :range-start="equiposRange.start"
            :range-end="equiposRange.end"
            :total-items="equiposTotalItems"
            @update:page="fetchEquipos"
          />
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Detallar Ubicación Técnica (Manual)</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Catálogo manual de nivel, código y rama para homologación SAP-like.</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                {{ detalleTotalItems }} registros
              </span>
              <button
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg shadow-primary/25"
                @click="openDetalleCreate"
              >
                <span class="material-symbols-outlined text-base">add</span>
                <span>Nuevo detalle</span>
              </button>
            </div>
          </div>

          <div class="px-6 py-4 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
              <select v-model="detalleUbicacionFiltros.nivel_1" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Nivel 1</option>
                <option v-for="nivel in detalleUbicacionNiveles.nivel_1" :key="`detalle-n1-${nivel}`" :value="nivel">
                  {{ nivel }}
                </option>
              </select>
              <select
                v-model="detalleUbicacionFiltros.nivel_2"
                class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                :disabled="!detalleUbicacionFiltros.nivel_1 || detalleUbicacionNiveles.nivel_2.length === 0"
              >
                <option value="">Nivel 2</option>
                <option v-for="nivel in detalleUbicacionNiveles.nivel_2" :key="`detalle-n2-${nivel}`" :value="nivel">
                  {{ nivel }}
                </option>
              </select>
              <select
                v-model="detalleUbicacionFiltros.nivel_3"
                class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                :disabled="!detalleUbicacionFiltros.nivel_2 || detalleUbicacionNiveles.nivel_3.length === 0"
              >
                <option value="">Nivel 3</option>
                <option v-for="nivel in detalleUbicacionNiveles.nivel_3" :key="`detalle-n3-${nivel}`" :value="nivel">
                  {{ nivel }}
                </option>
              </select>
              <input
                v-model.trim="detalleUbicacionFiltros.q_ubicacion"
                type="text"
                class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm"
                placeholder="Buscar ubicación (código/nombre/nivel)"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
              <input
                v-model="detalleQuery"
                type="text"
                class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm md:col-span-2"
                placeholder="Buscar código, nombre o descripción..."
              />
              <select v-model="detalleNivelFiltro" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Todos los niveles</option>
                <option v-for="n in [1,2,3,4,5,6,7,8]" :key="`filtro-nivel-${n}`" :value="String(n)">
                  Nivel {{ n }}
                </option>
              </select>
              <select v-model="detalleRamaFiltro" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Todas las ramas N3</option>
                <option value="--">-- (niveles 1-3)</option>
                <option v-for="rama in detalleUbicacionNiveles.nivel_3" :key="`filtro-rama-${rama}`" :value="rama">
                  {{ rama }}
                </option>
              </select>
              <select v-model="detalleOrigenFiltro" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Todos los orígenes</option>
                <option value="Homologado">Homologado</option>
                <option value="Detectado">Detectado</option>
              </select>
              <select v-model="detalleStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Todos</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
              </select>
            </div>

            <div class="flex items-center justify-between gap-3">
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">
                <template v-if="loadingDetalle">
                  Cargando detalle de niveles...
                </template>
                <template v-else>
                  Mostrando {{ detalleRange.start }}-{{ detalleRange.end }} de {{ detalleTotalItems }} registros.
                </template>
              </p>
              <button
                class="text-xs font-semibold text-primary hover:underline"
                @click="clearDetalleFilters"
              >
                Limpiar filtros
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">Nivel</th>
                  <th class="px-6 py-4">Código</th>
                  <th class="px-6 py-4">Rama N3</th>
                  <th class="px-6 py-4">Nombre / Descripción</th>
                  <th class="px-6 py-4">Origen</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="detalle in detalleNiveles" :key="detalle.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4 text-sm font-semibold">N{{ detalle.nivel }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="font-semibold">{{ detalle.codigo }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 dark:bg-border-dark/60 text-xs font-semibold text-slate-700 dark:text-[#92adc9]">
                      {{ detalle.rama_nivel_3 }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div>{{ detalle.nombre || '-' }}</div>
                    <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ detalle.descripcion || 'Sin descripción' }}</div>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="detalle.origen === 'Homologado' ? 'bg-primary/10 text-primary' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ detalle.origen }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="detalle.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ detalle.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openDetalleEdit(detalle)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button class="p-1.5 hover:text-red-500 transition-colors" @click="deleteDetalle(detalle)">
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!loadingDetalle && detalleNiveles.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="7">Sin registros</td>
                </tr>
                <tr v-if="loadingDetalle">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="7">Cargando detalle de niveles...</td>
                </tr>
              </tbody>
            </table>
          </div>

          <PaginationBar
            :page="detallePage"
            :total-pages="detalleTotalPages"
            :range-start="detalleRange.start"
            :range-end="detalleRange.end"
            :total-items="detalleTotalItems"
            @update:page="fetchDetalleNiveles"
          />
        </div>
      </div>

      <div class="flex justify-end">
        <button
          class="inline-flex items-center gap-2 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-300 px-4 py-2.5 rounded-lg font-bold text-sm transition-all hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="purgeRunning"
          @click="purgeNoUsados"
        >
          <span class="material-symbols-outlined">warning</span>
          <span>{{ purgeRunning ? 'Depurando...' : 'Depurar no usados' }}</span>
        </button>
      </div>
    </div>

    <div v-if="showUbicacionModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-5xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingUbicacionId ? 'Editar ubicación' : 'Nueva ubicación' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Código, nombre y nivel 1 son obligatorios.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeUbicacionModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="p-6 space-y-4 max-h-[75vh] overflow-auto">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <div class="mt-2 flex items-center gap-2">
                <input
                  v-model="ubicacionForm.codigo"
                  type="text"
                  class="w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm uppercase"
                  :class="ubicacionErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
                />
                <button
                  class="shrink-0 px-3 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-xs font-semibold hover:bg-slate-50 dark:hover:bg-border-dark"
                  @click="populateNivelesFromCodigo"
                >
                  Extraer niveles
                </button>
              </div>
              <p v-if="ubicacionErrors.codigo" class="mt-1 text-xs text-red-500">{{ ubicacionErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Activo</label>
              <select v-model="ubicacionForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
            <div class="md:col-span-3">
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="ubicacionForm.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="ubicacionErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="ubicacionErrors.nombre" class="mt-1 text-xs text-red-500">{{ ubicacionErrors.nombre }}</p>
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 dark:border-border-dark p-4">
            <h5 class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Niveles SAP-like</h5>
            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 1 (2)</label>
                <input v-model="ubicacionForm.nivel_1" maxlength="2" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm uppercase" :class="ubicacionErrors.nivel_1 ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'" />
                <p v-if="ubicacionErrors.nivel_1" class="mt-1 text-xs text-red-500">{{ ubicacionErrors.nivel_1 }}</p>
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 2 (4)</label>
                <input v-model="ubicacionForm.nivel_2" maxlength="4" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 3 (2)</label>
                <input v-model="ubicacionForm.nivel_3" maxlength="2" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 4 (3)</label>
                <input v-model="ubicacionForm.nivel_4" maxlength="3" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 5 (2)</label>
                <input v-model="ubicacionForm.nivel_5" maxlength="2" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 6 (5)</label>
                <input v-model="ubicacionForm.nivel_6" maxlength="5" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 7 (3)</label>
                <input v-model="ubicacionForm.nivel_7" maxlength="3" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
              <div>
                <label class="text-xs text-slate-500 dark:text-[#92adc9]">Nivel 8 (2)</label>
                <input v-model="ubicacionForm.nivel_8" maxlength="2" type="text" class="mt-1 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm uppercase" />
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeUbicacionModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveUbicacion">
            {{ editingUbicacionId ? 'Guardar cambios' : 'Crear ubicación' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showEquipoModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-3xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingEquipoId ? 'Editar equipo' : 'Nuevo equipo' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: código y nombre.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeEquipoModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="equipoForm.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="equipoErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="equipoErrors.codigo" class="mt-1 text-xs text-red-500">{{ equipoErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="equipoForm.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="equipoErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="equipoErrors.nombre" class="mt-1 text-xs text-red-500">{{ equipoErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Ubicación técnica</label>
              <select v-model="equipoForm.ubicacion_tecnica_id" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Sin ubicación</option>
                <option v-for="u in ubicacionesCatalogo" :key="u.id" :value="String(u.id)">
                  {{ u.codigo }} - {{ u.nombre }}
                </option>
              </select>
              <p v-if="equipoErrors.ubicacion_tecnica_id" class="mt-1 text-xs text-red-500">{{ equipoErrors.ubicacion_tecnica_id }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Área</label>
              <input v-model="equipoForm.area" type="text" maxlength="40" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Activo</label>
              <select v-model="equipoForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeEquipoModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveEquipo">
            {{ editingEquipoId ? 'Guardar cambios' : 'Crear equipo' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDetalleModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-3xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingDetalleId ? 'Editar detalle de nivel' : 'Nuevo detalle de nivel' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Registra significado por nivel/código y rama de nivel 3.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeDetalleModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nivel</label>
              <select v-model="detalleForm.nivel" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" :class="detalleErrors.nivel ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'">
                <option v-for="n in [1,2,3,4,5,6,7,8]" :key="`form-nivel-${n}`" :value="n">
                  Nivel {{ n }}
                </option>
              </select>
              <p v-if="detalleErrors.nivel" class="mt-1 text-xs text-red-500">{{ detalleErrors.nivel }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="detalleForm.codigo"
                type="text"
                maxlength="20"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm uppercase"
                :class="detalleErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="detalleErrors.codigo" class="mt-1 text-xs text-red-500">{{ detalleErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Rama nivel 3</label>
              <input
                v-model="detalleForm.rama_nivel_3"
                type="text"
                maxlength="2"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm uppercase"
                :class="detalleErrors.rama_nivel_3 ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
                :disabled="detalleForm.nivel <= 3"
              />
              <p v-if="detalleForm.nivel <= 3" class="mt-1 text-[11px] text-slate-500 dark:text-[#92adc9]">Para niveles 1 a 3 se usa automáticamente <span class="font-semibold">--</span>.</p>
              <p v-if="detalleErrors.rama_nivel_3" class="mt-1 text-xs text-red-500">{{ detalleErrors.rama_nivel_3 }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Origen</label>
              <select v-model="detalleForm.origen" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="Homologado">Homologado</option>
                <option value="Detectado">Detectado</option>
              </select>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Activo</label>
              <select v-model="detalleForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
          </div>

          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
            <input
              v-model="detalleForm.nombre"
              type="text"
              maxlength="160"
              class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
              :class="detalleErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
            />
            <p v-if="detalleErrors.nombre" class="mt-1 text-xs text-red-500">{{ detalleErrors.nombre }}</p>
          </div>

          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <textarea
              v-model="detalleForm.descripcion"
              rows="3"
              maxlength="255"
              class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
              :class="detalleErrors.descripcion ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
            ></textarea>
            <p v-if="detalleErrors.descripcion" class="mt-1 text-xs text-red-500">{{ detalleErrors.descripcion }}</p>
          </div>
        </div>

        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeDetalleModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveDetalle">
            {{ editingDetalleId ? 'Guardar cambios' : 'Crear detalle' }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="toast.visible"
      class="fixed bottom-6 right-6 z-50 rounded-lg px-4 py-3 text-sm font-semibold shadow-lg border"
      :class="toastClass"
    >
      {{ toast.message }}
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import PaginationBar from '../components/PaginationBar.vue';
import UbicacionCodigoTooltip from '../components/UbicacionCodigoTooltip.vue';
import { fetchConfiguracionSistema } from '../api/configuracion';

const perPage = ref(20);

const ubicaciones = ref([]);
const equipos = ref([]);
const detalleNiveles = ref([]);
const ubicacionesCatalogo = ref([]);
const ubicacionDetalleNivelesCatalogo = ref([]);

const loadingUbicaciones = ref(false);
const loadingEquipos = ref(false);
const loadingDetalle = ref(false);
const importRunning = ref(false);
const purgeRunning = ref(false);
const importZpm017Input = ref(null);

const ubicacionesQuery = ref('');
const ubicacionesStatus = ref('');
const ubicacionesUso = ref('');

const equiposQuery = ref('');
const equiposStatus = ref('');
const equiposUbicacion = ref('');
const equiposUso = ref('');

const detalleUbicacionNiveles = reactive({
  nivel_1: [],
  nivel_2: [],
  nivel_3: [],
});
const detalleUbicacionFiltros = reactive({
  nivel_1: '',
  nivel_2: '',
  nivel_3: '',
  q_ubicacion: '',
});
const detalleQuery = ref('');
const detalleNivelFiltro = ref('');
const detalleRamaFiltro = ref('');
const detalleOrigenFiltro = ref('');
const detalleStatus = ref('');

const ubicacionesPage = ref(1);
const ubicacionesTotalPages = ref(1);
const ubicacionesTotalItems = ref(0);
const ubicacionesRange = reactive({ start: 0, end: 0 });

const equiposPage = ref(1);
const equiposTotalPages = ref(1);
const equiposTotalItems = ref(0);
const equiposRange = reactive({ start: 0, end: 0 });

const detallePage = ref(1);
const detalleTotalPages = ref(1);
const detalleTotalItems = ref(0);
const detalleRange = reactive({ start: 0, end: 0 });

const showUbicacionModal = ref(false);
const showEquipoModal = ref(false);
const showDetalleModal = ref(false);
const editingUbicacionId = ref(null);
const editingEquipoId = ref(null);
const editingDetalleId = ref(null);

const ubicacionErrors = reactive({});
const equipoErrors = reactive({});
const detalleErrors = reactive({
  nivel: '',
  codigo: '',
  rama_nivel_3: '',
  nombre: '',
  descripcion: '',
  origen: '',
  activo: '',
});

const ubicacionForm = reactive({
  codigo: '',
  nombre: '',
  nivel_1: '',
  nivel_2: '',
  nivel_3: '',
  nivel_4: '',
  nivel_5: '',
  nivel_6: '',
  nivel_7: '',
  nivel_8: '',
  activo: 1,
});

const equipoForm = reactive({
  codigo: '',
  nombre: '',
  ubicacion_tecnica_id: '',
  area: '',
  activo: 1,
});

const detalleForm = reactive({
  nivel: 4,
  codigo: '',
  rama_nivel_3: '',
  nombre: '',
  descripcion: '',
  origen: 'Homologado',
  activo: 1,
});

const toast = reactive({
  visible: false,
  message: '',
  type: 'success',
});

const toastClass = computed(() => {
  if (toast.type === 'error') {
    return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800/60';
  }
  if (toast.type === 'info') {
    return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/60';
  }
  return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/60';
});

let ubicacionesSearchTimer = null;
let equiposSearchTimer = null;
let detalleSearchTimer = null;
let detalleUbicacionesSearchTimer = null;

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

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 2800);
};

const purgeNoUsados = async () => {
  if (purgeRunning.value) return;

  const confirm1 = window.confirm(
    'Acción de alto impacto: se eliminarán SOLO ubicaciones técnicas y equipos no usados en bitácora. No se eliminarán datos de Inventario NMS/EMS. ¿Deseas continuar?'
  );
  if (!confirm1) return;

  const confirm2 = window.confirm(
    'Confirmación 2/3: esta operación no se puede deshacer desde la app. ¿Seguro que deseas continuar?'
  );
  if (!confirm2) return;

  const confirm3 = window.confirm(
    'Confirmación 3/3: se eliminarán SOLO ubicaciones técnicas y equipos no usados en bitácora. ¿Ejecutar depuración ahora?'
  );
  if (!confirm3) return;

  purgeRunning.value = true;
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post('/api/v1/admin/depuracion/purgar-no-usados', {
      confirmaciones: 3,
      alcance: 'ubicaciones_equipos',
    });
    const data = response.data?.data || {};
    showToast(
      `Depuración completada. Equipos: ${data.equipos_eliminados || 0}, Ubicaciones: ${data.ubicaciones_eliminadas || 0}.`
    );

    await Promise.all([
      loadUbicacionesCatalogo(),
      fetchUbicaciones(1),
      fetchEquipos(1),
      fetchDetalleNiveles(1),
    ]);
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo ejecutar la depuración.', 'error');
  } finally {
    purgeRunning.value = false;
  }
};

const parseNivelesFromCodigo = (codigo) => {
  const normalized = String(codigo || '').trim().toUpperCase();
  if (!normalized) {
    return {
      nivel_1: null,
      nivel_2: null,
      nivel_3: null,
      nivel_4: null,
      nivel_5: null,
      nivel_6: null,
      nivel_7: null,
      nivel_8: null,
    };
  }

  const segments = normalized.split('-').filter(Boolean);
  const limit = (value, max) => {
    const clean = String(value || '').trim();
    return clean ? clean.slice(0, max) : null;
  };

  const nivel_1 = limit(segments[0], 2);
  const nivel_2 = limit(segments[1], 4);
  const nivel_3 = limit(segments[2], 2);
  const nivel_4 = limit(segments[3], 3);
  const nivel_5 = limit(segments[4], 2);

  let nivel_6 = null;
  let nivel_7 = null;
  let nivel_8 = null;

  const rest = segments.slice(5);
  if (rest.length >= 3) {
    nivel_8 = limit(rest[rest.length - 1], 2);
    nivel_7 = limit(rest[rest.length - 2], 3);
    nivel_6 = limit(rest.slice(0, -2).join('-'), 5);
  } else if (rest.length === 2) {
    nivel_6 = limit(rest[0], 5);
    nivel_7 = limit(rest[1], 3);
  } else if (rest.length === 1) {
    nivel_6 = limit(rest[0], 5);
  }

  return {
    nivel_1,
    nivel_2,
    nivel_3,
    nivel_4,
    nivel_5,
    nivel_6,
    nivel_7,
    nivel_8,
  };
};

const toNullableUpper = (value, max) => {
  const clean = String(value || '').trim().toUpperCase();
  if (!clean) return null;
  return clean.slice(0, max);
};

const normalizeValidationErrors = (target, errors) => {
  Object.keys(target).forEach((key) => {
    target[key] = '';
  });

  Object.entries(errors || {}).forEach(([field, messages]) => {
    target[field] = messages?.[0] || 'Dato inválido';
  });
};

const loadConfig = async () => {
  try {
    const config = await fetchConfiguracionSistema();
    const map = Object.fromEntries(config.map((item) => [item.clave, item.valor]));
    const per = Number(map['paginacion.default_per_page'] ?? 20);
    perPage.value = Number.isFinite(per) && per > 0 ? per : 20;
  } catch {
    perPage.value = 20;
  }
};

const loadUbicacionesCatalogo = async () => {
  try {
    const response = await window.axios.get('/api/v1/ubicaciones');
    const data = response.data?.data ?? [];
    ubicacionesCatalogo.value = [...data].sort((a, b) => String(a.codigo || '').localeCompare(String(b.codigo || '')));
  } catch {
    ubicacionesCatalogo.value = [];
  }
};

const loadUbicacionDetalleNivelesCatalogo = async () => {
  try {
    const response = await window.axios.get('/api/v1/ubicaciones/detalle-niveles');
    ubicacionDetalleNivelesCatalogo.value = response.data?.data ?? response.data ?? [];
  } catch {
    ubicacionDetalleNivelesCatalogo.value = [];
  }
};

const ensureUbicacionInCatalogo = (ubicacion) => {
  if (!ubicacion?.id) return;
  if (ubicacionesCatalogo.value.some((row) => String(row.id) === String(ubicacion.id))) return;
  ubicacionesCatalogo.value.push({
    id: ubicacion.id,
    codigo: ubicacion.codigo,
    nombre: ubicacion.nombre,
  });
  ubicacionesCatalogo.value.sort((a, b) => String(a.codigo || '').localeCompare(String(b.codigo || '')));
};

const ubicacionesParams = (page = 1) => ({
  page,
  per_page: perPage.value,
  q: ubicacionesQuery.value.trim() || undefined,
  activo: ubicacionesStatus.value === 'activo' ? '1' : (ubicacionesStatus.value === 'inactivo' ? '0' : undefined),
  uso: ubicacionesUso.value || undefined,
});

const equiposParams = (page = 1) => ({
  page,
  per_page: perPage.value,
  q: equiposQuery.value.trim() || undefined,
  ubicacion_tecnica_id: equiposUbicacion.value || undefined,
  activo: equiposStatus.value === 'activo' ? '1' : (equiposStatus.value === 'inactivo' ? '0' : undefined),
  uso: equiposUso.value || undefined,
});

const detalleParams = (page = 1) => ({
  page,
  per_page: perPage.value,
  q: detalleQuery.value.trim() || undefined,
  nivel: detalleNivelFiltro.value || undefined,
  rama_nivel_3: detalleRamaFiltro.value || undefined,
  origen: detalleOrigenFiltro.value || undefined,
  activo: detalleStatus.value === 'activo' ? '1' : (detalleStatus.value === 'inactivo' ? '0' : undefined),
  nivel_1: detalleUbicacionFiltros.nivel_1 || undefined,
  nivel_2: detalleUbicacionFiltros.nivel_2 || undefined,
  nivel_3: detalleUbicacionFiltros.nivel_3 || undefined,
  q_ubicacion: detalleUbicacionFiltros.q_ubicacion.trim() || undefined,
});

const loadDetalleNivel1Ubicaciones = async () => {
  try {
    const response = await window.axios.get('/api/v1/ubicaciones', {
      params: { distinct_nivel: 1 },
    });
    detalleUbicacionNiveles.nivel_1 = response.data?.data ?? [];
  } catch {
    detalleUbicacionNiveles.nivel_1 = [];
  }
};

const loadDetalleNivel2Ubicaciones = async () => {
  if (!detalleUbicacionFiltros.nivel_1) {
    detalleUbicacionNiveles.nivel_2 = [];
    return;
  }

  try {
    const response = await window.axios.get('/api/v1/ubicaciones', {
      params: {
        distinct_nivel: 2,
        nivel_1: detalleUbicacionFiltros.nivel_1,
      },
    });
    detalleUbicacionNiveles.nivel_2 = response.data?.data ?? [];
  } catch {
    detalleUbicacionNiveles.nivel_2 = [];
  }
};

const loadDetalleNivel3Ubicaciones = async () => {
  if (!detalleUbicacionFiltros.nivel_1 || !detalleUbicacionFiltros.nivel_2) {
    detalleUbicacionNiveles.nivel_3 = [];
    return;
  }

  try {
    const response = await window.axios.get('/api/v1/ubicaciones', {
      params: {
        distinct_nivel: 3,
        nivel_1: detalleUbicacionFiltros.nivel_1,
        nivel_2: detalleUbicacionFiltros.nivel_2,
      },
    });
    detalleUbicacionNiveles.nivel_3 = response.data?.data ?? [];
  } catch {
    detalleUbicacionNiveles.nivel_3 = [];
  }
};

const fetchUbicaciones = async (targetPage = 1) => {
  loadingUbicaciones.value = true;
  try {
    const response = await window.axios.get('/api/v1/admin/dm/ubicaciones', { params: ubicacionesParams(targetPage) });
    const data = response.data?.data ?? [];
    const meta = response.data?.meta ?? {};

    ubicaciones.value = data;
    ubicacionesPage.value = meta.current_page ?? targetPage;
    ubicacionesTotalPages.value = meta.last_page ?? 1;
    ubicacionesTotalItems.value = meta.total ?? data.length;
    ubicacionesRange.start = meta.from ?? (data.length ? 1 : 0);
    ubicacionesRange.end = meta.to ?? data.length;
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudieron cargar ubicaciones.', 'error');
  } finally {
    loadingUbicaciones.value = false;
  }
};

const fetchEquipos = async (targetPage = 1) => {
  loadingEquipos.value = true;
  try {
    const response = await window.axios.get('/api/v1/admin/dm/equipos', { params: equiposParams(targetPage) });
    const data = response.data?.data ?? [];
    const meta = response.data?.meta ?? {};

    equipos.value = data;
    equiposPage.value = meta.current_page ?? targetPage;
    equiposTotalPages.value = meta.last_page ?? 1;
    equiposTotalItems.value = meta.total ?? data.length;
    equiposRange.start = meta.from ?? (data.length ? 1 : 0);
    equiposRange.end = meta.to ?? data.length;
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudieron cargar equipos.', 'error');
  } finally {
    loadingEquipos.value = false;
  }
};

const fetchDetalleNiveles = async (targetPage = 1) => {
  loadingDetalle.value = true;
  try {
    const response = await window.axios.get('/api/v1/admin/dm/detalle-niveles', { params: detalleParams(targetPage) });
    const data = response.data?.data ?? [];
    const meta = response.data?.meta ?? {};

    detalleNiveles.value = data;
    detallePage.value = meta.current_page ?? targetPage;
    detalleTotalPages.value = meta.last_page ?? 1;
    detalleTotalItems.value = meta.total ?? data.length;
    detalleRange.start = meta.from ?? (data.length ? 1 : 0);
    detalleRange.end = meta.to ?? data.length;
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo cargar el detalle de niveles.', 'error');
  } finally {
    loadingDetalle.value = false;
  }
};

const clearUbicacionesFilters = () => {
  ubicacionesQuery.value = '';
  ubicacionesStatus.value = '';
  ubicacionesUso.value = '';
  fetchUbicaciones(1);
};

const clearEquiposFilters = () => {
  equiposQuery.value = '';
  equiposStatus.value = '';
  equiposUbicacion.value = '';
  equiposUso.value = '';
  fetchEquipos(1);
};

const clearDetalleFilters = async () => {
  detalleUbicacionFiltros.nivel_1 = '';
  detalleUbicacionFiltros.nivel_2 = '';
  detalleUbicacionFiltros.nivel_3 = '';
  detalleUbicacionFiltros.q_ubicacion = '';
  detalleQuery.value = '';
  detalleNivelFiltro.value = '';
  detalleRamaFiltro.value = '';
  detalleOrigenFiltro.value = '';
  detalleStatus.value = '';
  detalleUbicacionNiveles.nivel_2 = [];
  detalleUbicacionNiveles.nivel_3 = [];
  await fetchDetalleNiveles(1);
};

const clearUbicacionErrors = () => normalizeValidationErrors(ubicacionErrors, {});
const clearEquipoErrors = () => normalizeValidationErrors(equipoErrors, {});
const clearDetalleErrors = () => normalizeValidationErrors(detalleErrors, {});

const resetUbicacionForm = () => {
  ubicacionForm.codigo = '';
  ubicacionForm.nombre = '';
  ubicacionForm.nivel_1 = '';
  ubicacionForm.nivel_2 = '';
  ubicacionForm.nivel_3 = '';
  ubicacionForm.nivel_4 = '';
  ubicacionForm.nivel_5 = '';
  ubicacionForm.nivel_6 = '';
  ubicacionForm.nivel_7 = '';
  ubicacionForm.nivel_8 = '';
  ubicacionForm.activo = 1;
  clearUbicacionErrors();
};

const resetEquipoForm = () => {
  equipoForm.codigo = '';
  equipoForm.nombre = '';
  equipoForm.ubicacion_tecnica_id = '';
  equipoForm.area = '';
  equipoForm.activo = 1;
  clearEquipoErrors();
};

const syncDetalleRamaFromNivel = () => {
  if (Number(detalleForm.nivel) <= 3) {
    detalleForm.rama_nivel_3 = '--';
  } else if (detalleForm.rama_nivel_3 === '--') {
    detalleForm.rama_nivel_3 = '';
  }
};

const resetDetalleForm = () => {
  detalleForm.nivel = 4;
  detalleForm.codigo = '';
  detalleForm.rama_nivel_3 = '';
  detalleForm.nombre = '';
  detalleForm.descripcion = '';
  detalleForm.origen = 'Homologado';
  detalleForm.activo = 1;
  clearDetalleErrors();
  syncDetalleRamaFromNivel();
};

const populateNivelesFromCodigo = () => {
  const parsed = parseNivelesFromCodigo(ubicacionForm.codigo);
  ubicacionForm.nivel_1 = parsed.nivel_1 || '';
  ubicacionForm.nivel_2 = parsed.nivel_2 || '';
  ubicacionForm.nivel_3 = parsed.nivel_3 || '';
  ubicacionForm.nivel_4 = parsed.nivel_4 || '';
  ubicacionForm.nivel_5 = parsed.nivel_5 || '';
  ubicacionForm.nivel_6 = parsed.nivel_6 || '';
  ubicacionForm.nivel_7 = parsed.nivel_7 || '';
  ubicacionForm.nivel_8 = parsed.nivel_8 || '';
};

const openUbicacionCreate = () => {
  resetUbicacionForm();
  editingUbicacionId.value = null;
  showUbicacionModal.value = true;
};

const openUbicacionEdit = (ubicacion) => {
  resetUbicacionForm();
  editingUbicacionId.value = ubicacion.id;

  ubicacionForm.codigo = ubicacion.codigo || '';
  ubicacionForm.nombre = ubicacion.nombre || '';
  ubicacionForm.nivel_1 = ubicacion.nivel_1 || '';
  ubicacionForm.nivel_2 = ubicacion.nivel_2 || '';
  ubicacionForm.nivel_3 = ubicacion.nivel_3 || '';
  ubicacionForm.nivel_4 = ubicacion.nivel_4 || '';
  ubicacionForm.nivel_5 = ubicacion.nivel_5 || '';
  ubicacionForm.nivel_6 = ubicacion.nivel_6 || '';
  ubicacionForm.nivel_7 = ubicacion.nivel_7 || '';
  ubicacionForm.nivel_8 = ubicacion.nivel_8 || '';
  ubicacionForm.activo = ubicacion.activo ? 1 : 0;

  showUbicacionModal.value = true;
};

const closeUbicacionModal = () => {
  showUbicacionModal.value = false;
};

const openEquipoCreate = () => {
  resetEquipoForm();
  editingEquipoId.value = null;
  showEquipoModal.value = true;
};

const openDetalleCreate = () => {
  resetDetalleForm();
  editingDetalleId.value = null;
  showDetalleModal.value = true;
};

const openEquipoEdit = (equipo) => {
  resetEquipoForm();
  editingEquipoId.value = equipo.id;

  ensureUbicacionInCatalogo(equipo.ubicacion);

  equipoForm.codigo = equipo.codigo || '';
  equipoForm.nombre = equipo.nombre || '';
  equipoForm.ubicacion_tecnica_id = equipo.ubicacion_tecnica_id ? String(equipo.ubicacion_tecnica_id) : '';
  equipoForm.area = equipo.area || '';
  equipoForm.activo = equipo.activo ? 1 : 0;

  showEquipoModal.value = true;
};

const openDetalleEdit = (detalle) => {
  resetDetalleForm();
  editingDetalleId.value = detalle.id;

  detalleForm.nivel = Number(detalle.nivel) || 4;
  detalleForm.codigo = detalle.codigo || '';
  detalleForm.rama_nivel_3 = detalle.rama_nivel_3 || '';
  detalleForm.nombre = detalle.nombre || '';
  detalleForm.descripcion = detalle.descripcion || '';
  detalleForm.origen = detalle.origen || 'Homologado';
  detalleForm.activo = detalle.activo ? 1 : 0;
  syncDetalleRamaFromNivel();

  showDetalleModal.value = true;
};

const closeEquipoModal = () => {
  showEquipoModal.value = false;
};

const closeDetalleModal = () => {
  showDetalleModal.value = false;
};

const buildUbicacionPayload = () => {
  const parsed = parseNivelesFromCodigo(ubicacionForm.codigo);

  return {
    codigo: String(ubicacionForm.codigo || '').trim(),
    nombre: String(ubicacionForm.nombre || '').trim(),
    nivel_1: toNullableUpper(ubicacionForm.nivel_1 || parsed.nivel_1, 2),
    nivel_2: toNullableUpper(ubicacionForm.nivel_2 || parsed.nivel_2, 4),
    nivel_3: toNullableUpper(ubicacionForm.nivel_3 || parsed.nivel_3, 2),
    nivel_4: toNullableUpper(ubicacionForm.nivel_4 || parsed.nivel_4, 3),
    nivel_5: toNullableUpper(ubicacionForm.nivel_5 || parsed.nivel_5, 2),
    nivel_6: toNullableUpper(ubicacionForm.nivel_6 || parsed.nivel_6, 5),
    nivel_7: toNullableUpper(ubicacionForm.nivel_7 || parsed.nivel_7, 3),
    nivel_8: toNullableUpper(ubicacionForm.nivel_8 || parsed.nivel_8, 2),
    activo: ubicacionForm.activo ? 1 : 0,
  };
};

const buildEquipoPayload = () => ({
  codigo: String(equipoForm.codigo || '').trim(),
  nombre: String(equipoForm.nombre || '').trim(),
  ubicacion_tecnica_id: equipoForm.ubicacion_tecnica_id ? Number(equipoForm.ubicacion_tecnica_id) : null,
  area: String(equipoForm.area || '').trim() || null,
  activo: equipoForm.activo ? 1 : 0,
});

const buildDetallePayload = () => {
  const nivel = Number(detalleForm.nivel) || 1;
  const rama = nivel <= 3
    ? '--'
    : String(detalleForm.rama_nivel_3 || '').trim().toUpperCase();

  return {
    nivel,
    codigo: String(detalleForm.codigo || '').trim().toUpperCase(),
    rama_nivel_3: rama || null,
    nombre: String(detalleForm.nombre || '').trim() || null,
    descripcion: String(detalleForm.descripcion || '').trim() || null,
    origen: String(detalleForm.origen || 'Homologado').trim() || 'Homologado',
    activo: detalleForm.activo ? 1 : 0,
  };
};

const saveUbicacion = async () => {
  clearUbicacionErrors();
  const payload = buildUbicacionPayload();

  if (!payload.codigo) {
    ubicacionErrors.codigo = 'El código es obligatorio.';
    return;
  }
  if (!payload.nombre) {
    ubicacionErrors.nombre = 'El nombre es obligatorio.';
    return;
  }
  if (!payload.nivel_1) {
    ubicacionErrors.nivel_1 = 'El nivel 1 es obligatorio.';
    return;
  }

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingUbicacionId.value) {
      await window.axios.put(`/api/v1/admin/dm/ubicaciones/${editingUbicacionId.value}`, payload);
      showToast('Ubicación actualizada.');
    } else {
      await window.axios.post('/api/v1/admin/dm/ubicaciones', payload);
      showToast('Ubicación creada.');
    }

    showUbicacionModal.value = false;
    await Promise.all([fetchUbicaciones(ubicacionesPage.value), loadUbicacionesCatalogo()]);
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    normalizeValidationErrors(ubicacionErrors, errors);

    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar la ubicación.', 'error');
    }
  }
};

const saveEquipo = async () => {
  clearEquipoErrors();
  const payload = buildEquipoPayload();

  if (!payload.codigo) {
    equipoErrors.codigo = 'El código es obligatorio.';
    return;
  }
  if (!payload.nombre) {
    equipoErrors.nombre = 'El nombre es obligatorio.';
    return;
  }

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingEquipoId.value) {
      await window.axios.put(`/api/v1/admin/dm/equipos/${editingEquipoId.value}`, payload);
      showToast('Equipo actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/dm/equipos', payload);
      showToast('Equipo creado.');
    }

    showEquipoModal.value = false;
    await fetchEquipos(equiposPage.value);
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    normalizeValidationErrors(equipoErrors, errors);

    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar el equipo.', 'error');
    }
  }
};

const saveDetalle = async () => {
  clearDetalleErrors();
  const payload = buildDetallePayload();

  if (!payload.codigo) {
    detalleErrors.codigo = 'El código es obligatorio.';
    return;
  }
  if (payload.nivel >= 4 && !payload.rama_nivel_3) {
    detalleErrors.rama_nivel_3 = 'La rama de nivel 3 es obligatoria para niveles 4 a 8.';
    return;
  }

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingDetalleId.value) {
      await window.axios.put(`/api/v1/admin/dm/detalle-niveles/${editingDetalleId.value}`, payload);
      showToast('Detalle de nivel actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/dm/detalle-niveles', payload);
      showToast('Detalle de nivel creado.');
    }

    showDetalleModal.value = false;
    await fetchDetalleNiveles(detallePage.value);
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    normalizeValidationErrors(detalleErrors, errors);

    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar el detalle de nivel.', 'error');
    }
  }
};

const deleteUbicacion = async (ubicacion) => {
  if (ubicacion.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }

  const confirmed = window.confirm(`¿Eliminar la ubicación "${ubicacion.nombre}"?`);
  if (!confirmed) return;

  try {
    await window.axios.delete(`/api/v1/admin/dm/ubicaciones/${ubicacion.id}`);
    showToast('Ubicación eliminada.');
    await Promise.all([fetchUbicaciones(ubicacionesPage.value), loadUbicacionesCatalogo()]);
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar la ubicación.', 'error');
  }
};

const deleteEquipo = async (equipo) => {
  if (equipo.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }

  const confirmed = window.confirm(`¿Eliminar el equipo "${equipo.nombre}"?`);
  if (!confirmed) return;

  try {
    await window.axios.delete(`/api/v1/admin/dm/equipos/${equipo.id}`);
    showToast('Equipo eliminado.');
    await fetchEquipos(equiposPage.value);
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar el equipo.', 'error');
  }
};

const deleteDetalle = async (detalle) => {
  const confirmed = window.confirm(`¿Eliminar el detalle N${detalle.nivel} ${detalle.codigo} (${detalle.rama_nivel_3})?`);
  if (!confirmed) return;

  try {
    await window.axios.delete(`/api/v1/admin/dm/detalle-niveles/${detalle.id}`);
    showToast('Detalle de nivel eliminado.');
    await fetchDetalleNiveles(detallePage.value);
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar el detalle de nivel.', 'error');
  }
};

const formatNiveles = (item) => {
  const levels = [
    item.nivel_1,
    item.nivel_2,
    item.nivel_3,
    item.nivel_4,
    item.nivel_5,
    item.nivel_6,
    item.nivel_7,
    item.nivel_8,
  ].filter(Boolean);

  return levels.length ? levels.join(' - ') : '-';
};

const formatDateTime = (value) => {
  if (!value) return 'Sin sincronización';
  try {
    return new Intl.DateTimeFormat('es-MX', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value));
  } catch {
    return value;
  }
};

const onImportarZpm017 = () => {
  if (importRunning.value) return;
  importZpm017Input.value?.click();
};

const onImportarZpm017File = async (event) => {
  const file = event?.target?.files?.[0];
  if (!file) return;

  // Permite seleccionar el mismo archivo otra vez.
  event.target.value = '';

  const formData = new FormData();
  formData.append('archivo', file);

  importRunning.value = true;
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post('/api/v1/admin/dm/importar-zpm017', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const totals = response.data?.data?.totals || {};
    const equipos = Number(totals.equipos_upsertados || 0);
    const ubicaciones = Number(totals.ubicaciones_upsertadas || 0);
    showToast(`Importación ZPM017 completada: ${equipos} equipos y ${ubicaciones} ubicaciones procesadas.`);

    await Promise.all([
      loadUbicacionesCatalogo(),
      fetchUbicaciones(1),
      fetchEquipos(1),
      fetchDetalleNiveles(1),
    ]);
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo importar el archivo ZPM017.', 'error');
  } finally {
    importRunning.value = false;
  }
};

watch(ubicacionesQuery, () => {
  if (ubicacionesSearchTimer) {
    clearTimeout(ubicacionesSearchTimer);
  }
  ubicacionesSearchTimer = window.setTimeout(() => {
    fetchUbicaciones(1);
  }, 300);
});

watch([ubicacionesStatus, ubicacionesUso], () => {
  fetchUbicaciones(1);
});

watch(equiposQuery, () => {
  if (equiposSearchTimer) {
    clearTimeout(equiposSearchTimer);
  }
  equiposSearchTimer = window.setTimeout(() => {
    fetchEquipos(1);
  }, 300);
});

watch([equiposStatus, equiposUbicacion, equiposUso], () => {
  fetchEquipos(1);
});

watch(detalleQuery, () => {
  if (detalleSearchTimer) {
    clearTimeout(detalleSearchTimer);
  }
  detalleSearchTimer = window.setTimeout(() => {
    fetchDetalleNiveles(1);
  }, 300);
});

watch(() => detalleUbicacionFiltros.q_ubicacion, () => {
  if (detalleUbicacionesSearchTimer) {
    clearTimeout(detalleUbicacionesSearchTimer);
  }
  detalleUbicacionesSearchTimer = window.setTimeout(() => {
    fetchDetalleNiveles(1);
  }, 300);
});

watch(
  () => detalleUbicacionFiltros.nivel_1,
  async () => {
    detalleUbicacionFiltros.nivel_2 = '';
    detalleUbicacionFiltros.nivel_3 = '';
    detalleUbicacionNiveles.nivel_3 = [];
    await loadDetalleNivel2Ubicaciones();
    await fetchDetalleNiveles(1);
  }
);

watch(
  () => detalleUbicacionFiltros.nivel_2,
  async () => {
    detalleUbicacionFiltros.nivel_3 = '';
    await loadDetalleNivel3Ubicaciones();
    await fetchDetalleNiveles(1);
  }
);

watch(
  () => detalleUbicacionFiltros.nivel_3,
  () => {
    fetchDetalleNiveles(1);
  }
);

watch([detalleNivelFiltro, detalleRamaFiltro, detalleOrigenFiltro, detalleStatus], () => {
  fetchDetalleNiveles(1);
});

watch(
  () => detalleForm.nivel,
  () => {
    syncDetalleRamaFromNivel();
  }
);

onMounted(async () => {
  await loadConfig();
  await Promise.all([
    loadUbicacionDetalleNivelesCatalogo(),
    loadDetalleNivel1Ubicaciones(),
    loadUbicacionesCatalogo(),
    fetchUbicaciones(1),
    fetchEquipos(1),
    fetchDetalleNiveles(1),
  ]);
});
</script>
