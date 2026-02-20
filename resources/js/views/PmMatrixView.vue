<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Matriz PM</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Editor de Mantenimiento de Planta</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Configurar las reglas para las clases de Órdenes y Actividad. (SAP PM-Like).</p>
        </div>
        <button
          class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-lg font-bold text-sm transition-all transform active:scale-95 shadow-lg shadow-primary/25"
          @click="openMatrizCreate"
        >
          <span class="material-symbols-outlined">add</span>
          <span>Asignar actividad a orden</span>
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
          <p class="text-slate-500 dark:text-[#92adc9] text-sm font-medium">Total Clases de Orden</p>
          <div class="flex items-end justify-between mt-2">
            <span class="text-3xl font-bold">{{ stats.ordenes }}</span>
            <button
              class="text-green-500 text-xs font-bold flex items-center bg-green-500/10 px-2 py-1 rounded-full hover:bg-green-500/20 transition-colors"
              @click="openOrdenCreate"
            >
              Nueva Orden
            </button>
          </div>
        </div>
        <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
          <p class="text-slate-500 dark:text-[#92adc9] text-sm font-medium">Total Clases de Actividad</p>
          <div class="flex items-end justify-between mt-2">
            <span class="text-3xl font-bold">{{ stats.actividades }}</span>
            <button
              class="text-blue-400 text-xs font-bold flex items-center bg-blue-500/10 px-2 py-1 rounded-full hover:bg-blue-500/20 transition-colors"
              @click="openActividadCreate"
            >
              Nueva Actividad
            </button>
          </div>
        </div>
        <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
          <p class="text-slate-500 dark:text-[#92adc9] text-sm font-medium">Actividades sin Orden</p>
          <div class="flex items-end justify-between mt-2">
            <span class="text-3xl font-bold">{{ stats.actividadesSinOrden }}</span>
            <span class="text-amber-500 text-xs font-bold flex items-center bg-amber-500/10 px-2 py-1 rounded-full">Requiere Acción</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Clases de Orden</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Para agrupar órdenes con características similares.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <input
                v-model="ordenesQuery"
                type="text"
                class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
                placeholder="Buscar orden..."
              />
              <select v-model="ordenesStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todas</option>
                <option value="activo">Activas</option>
                <option value="inactivo">Inactivas</option>
              </select>
              <button
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg shadow-primary/25"
                @click="openOrdenCreate"
              >
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Nueva</span>
              </button>
            </div>
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
                <tr v-for="orden in paginatedOrdenes" :key="orden.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ orden.nombre }}</div>
                    <div v-if="orden.descripcion" class="text-xs text-slate-500 dark:text-[#92adc9]">{{ orden.descripcion }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="orden.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ orden.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <span v-if="orden.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openOrdenEdit(orden)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="orden.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="orden.in_use"
                        :title="orden.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar'"
                        @click="deleteOrden(orden)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredOrdenes.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="3">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="ordenesPage"
            :total-pages="ordenesTotalPages"
            :range-start="ordenesRange.start"
            :range-end="ordenesRange.end"
            :total-items="filteredOrdenes.length"
            @update:page="ordenesPage = $event"
          />
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Clases de Actividad</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Establecer las actividades de los trabajos y/o procesos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <input
                v-model="actividadesQuery"
                type="text"
                class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
                placeholder="Buscar actividad..."
              />
              <select v-model="actividadesStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todas</option>
                <option value="activo">Activas</option>
                <option value="inactivo">Inactivas</option>
              </select>
              <button
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg shadow-primary/25"
                @click="openActividadCreate"
              >
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Nueva</span>
              </button>
            </div>
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
                <tr v-for="actividad in paginatedActividades" :key="actividad.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ actividad.nombre }}</div>
                    <div v-if="actividad.descripcion" class="text-xs text-slate-500 dark:text-[#92adc9]">{{ actividad.descripcion }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="actividad.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ actividad.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <span v-if="actividad.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openActividadEdit(actividad)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="actividad.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="actividad.in_use"
                        :title="actividad.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar'"
                        @click="deleteActividad(actividad)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredActividades.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="3">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="actividadesPage"
            :total-pages="actividadesTotalPages"
            :range-start="actividadesRange.start"
            :range-end="actividadesRange.end"
            :total-items="filteredActividades.length"
            @update:page="actividadesPage = $event"
          />
        </div>
      </div>

      <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <h4 class="font-bold text-lg">Resumen por actividad</h4>
          <div class="flex flex-wrap items-center gap-2">
            <select v-model="filterOrdenId" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todas las órdenes</option>
              <option v-for="orden in ordenes" :key="orden.id" :value="String(orden.id)">{{ orden.nombre }}</option>
            </select>
            <select v-model="filterActividadId" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todas las actividades</option>
              <option v-for="actividad in actividades" :key="actividad.id" :value="String(actividad.id)">{{ actividad.nombre }}</option>
            </select>
            <select v-model="resumenStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Estado</option>
              <option value="activo">Activas</option>
              <option value="pendiente">Pendientes</option>
            </select>
            <button class="px-3 py-1.5 border border-slate-200 dark:border-border-dark rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-border-dark transition-colors" @click="clearFilters">
              Limpiar
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
              <tr>
                <th class="px-6 py-4">Actividad</th>
                <th class="px-6 py-4">Clases de Orden</th>
                <th class="px-6 py-4">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
              <tr v-for="row in paginatedResumenRows" :key="row.actividadId" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                <td class="px-6 py-4 font-medium text-sm">{{ row.actividadNombre }}</td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="orden in row.ordenes"
                      :key="orden"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary"
                    >
                      {{ orden }}
                    </span>
                    <span v-if="row.ordenes.length === 0" class="text-xs text-slate-500 dark:text-[#92adc9]">Sin asignar</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="row.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'"
                  >
                    {{ row.activo ? 'Activo' : 'Pendiente' }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredMatrizRows.length === 0">
                <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="3">Sin registros</td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationBar
          :page="resumenPage"
          :total-pages="resumenTotalPages"
          :range-start="resumenRange.start"
          :range-end="resumenRange.end"
          :total-items="filteredMatrizRows.length"
          @update:page="resumenPage = $event"
        />
      </div>

      <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Relaciones Orden - Actividad</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Gestión de la matriz de combinaciones entre clases de orden y actividad.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <select v-model="filterOrdenId" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todas las órdenes</option>
                <option v-for="orden in ordenes" :key="orden.id" :value="String(orden.id)">{{ orden.nombre }}</option>
              </select>
              <select v-model="filterActividadId" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
                <option value="">Todas las actividades</option>
                <option v-for="actividad in actividades" :key="actividad.id" :value="String(actividad.id)">{{ actividad.nombre }}</option>
              </select>
              <select v-model="matrizStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
                <option value="">Estado</option>
                <option value="activo">Activas</option>
                <option value="inactivo">Inactivas</option>
              </select>
              <button
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg shadow-primary/25"
                @click="openMatrizCreate"
              >
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Asignar</span>
              </button>
            </div>
          </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
              <tr>
                <th class="px-6 py-4">Clase de Orden</th>
                <th class="px-6 py-4">Clase de Actividad</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
              <tr v-for="item in paginatedMatrizItems" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                <td class="px-6 py-4 text-sm font-medium">{{ item.ordenNombre }}</td>
                <td class="px-6 py-4 text-sm">{{ item.actividadNombre }}</td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="item.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'"
                  >
                    {{ item.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                  <span v-if="item.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                    En uso
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button class="p-1.5 hover:text-primary transition-colors" @click="openMatrizEdit(item)">
                      <span class="material-symbols-outlined text-lg">edit</span>
                    </button>
                    <button
                      class="p-1.5 transition-colors"
                      :class="item.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                      :disabled="item.in_use"
                      :title="item.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar combinación'"
                      @click="deleteMatriz(item)"
                    >
                      <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredMatrizItems.length === 0">
                <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="4">Sin registros</td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationBar
          :page="matrizPage"
          :total-pages="matrizTotalPages"
          :range-start="matrizRange.start"
          :range-end="matrizRange.end"
          :total-items="filteredMatrizItems.length"
          @update:page="matrizPage = $event"
        />
      </div>
    </div>

    <div v-if="showOrdenModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingOrdenId ? 'Editar clase de orden' : 'Nueva clase de orden' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Define nombre, descripcion y estado.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeOrdenModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
            <input v-model="ordenForm.nombre" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" :class="ordenErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'" />
            <p v-if="ordenErrors.nombre" class="mt-1 text-xs text-red-500">{{ ordenErrors.nombre }}</p>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <input v-model="ordenForm.descripcion" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
            <select v-model="ordenForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option :value="1">Activo</option>
              <option :value="0">Inactivo</option>
            </select>
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeOrdenModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveOrden">
            {{ editingOrdenId ? 'Guardar cambios' : 'Crear' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showActividadModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingActividadId ? 'Editar clase de actividad' : 'Nueva clase de actividad' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Define nombre, descripcion y estado.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeActividadModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
            <input v-model="actividadForm.nombre" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" :class="actividadErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'" />
            <p v-if="actividadErrors.nombre" class="mt-1 text-xs text-red-500">{{ actividadErrors.nombre }}</p>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <input v-model="actividadForm.descripcion" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
            <select v-model="actividadForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option :value="1">Activo</option>
              <option :value="0">Inactivo</option>
            </select>
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeActividadModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveActividad">
            {{ editingActividadId ? 'Guardar cambios' : 'Crear' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showMatrizModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingMatrizId ? 'Editar combinacion' : 'Nueva combinacion' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Relaciona una clase de orden con una actividad.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeMatrizModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de Orden</label>
            <select v-model="matrizForm.pm_clase_orden_id" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" :class="matrizErrors.pm_clase_orden_id ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'">
              <option value="">Selecciona</option>
              <option
                v-for="orden in ordenes"
                :key="orden.id"
                :value="orden.id"
                :disabled="!orden.activo && String(orden.id) !== String(matrizForm.pm_clase_orden_id)"
              >
                {{ orden.nombre }}{{ orden.activo ? '' : ' (Inactiva)' }}
              </option>
            </select>
            <p v-if="matrizErrors.pm_clase_orden_id" class="mt-1 text-xs text-red-500">{{ matrizErrors.pm_clase_orden_id }}</p>
            <p v-if="selectedOrdenInactive" class="mt-1 text-xs text-amber-500">No se puede relacionar una orden inactiva.</p>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Clase de Actividad</label>
            <select v-model="matrizForm.pm_clase_actividad_id" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" :class="matrizErrors.pm_clase_actividad_id ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'">
              <option value="">Selecciona</option>
              <option
                v-for="actividad in actividades"
                :key="actividad.id"
                :value="actividad.id"
                :disabled="!actividad.activo && String(actividad.id) !== String(matrizForm.pm_clase_actividad_id)"
              >
                {{ actividad.nombre }}{{ actividad.activo ? '' : ' (Inactiva)' }}
              </option>
            </select>
            <p v-if="matrizErrors.pm_clase_actividad_id" class="mt-1 text-xs text-red-500">{{ matrizErrors.pm_clase_actividad_id }}</p>
            <p v-if="selectedActividadInactive" class="mt-1 text-xs text-amber-500">No se puede relacionar una actividad inactiva.</p>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
            <select v-model="matrizForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
              <option :value="1">Activo</option>
              <option :value="0">Inactivo</option>
            </select>
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeMatrizModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveMatriz">
            {{ editingMatrizId ? 'Guardar cambios' : 'Crear' }}
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

const stats = reactive({
  ordenes: 0,
  actividades: 0,
  actividadesSinOrden: 0,
});

const ordenes = ref([]);
const actividades = ref([]);
const matriz = ref([]);
const matrizRows = ref([]);
const matrizItems = ref([]);
const ordenesQuery = ref('');
const ordenesStatus = ref('');
const actividadesQuery = ref('');
const actividadesStatus = ref('');
const filterOrdenId = ref('');
const filterActividadId = ref('');
const resumenStatus = ref('');
const matrizStatus = ref('');
const perPage = ref(20);
const ordenesPage = ref(1);
const actividadesPage = ref(1);
const resumenPage = ref(1);
const matrizPage = ref(1);

const showOrdenModal = ref(false);
const showActividadModal = ref(false);
const showMatrizModal = ref(false);
const editingOrdenId = ref(null);
const editingActividadId = ref(null);
const editingMatrizId = ref(null);

const ordenForm = reactive({
  nombre: '',
  descripcion: '',
  activo: 1,
});

const actividadForm = reactive({
  nombre: '',
  descripcion: '',
  activo: 1,
});

const matrizForm = reactive({
  pm_clase_orden_id: '',
  pm_clase_actividad_id: '',
  activo: 1,
});

const ordenErrors = reactive({});
const actividadErrors = reactive({});
const matrizErrors = reactive({});

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

  const [ordenesRes, actividadesRes, matrizRes] = await Promise.all([
    window.axios.get('/api/v1/admin/pm/ordenes'),
    window.axios.get('/api/v1/admin/pm/actividades'),
    window.axios.get('/api/v1/admin/pm/matriz'),
  ]);

  ordenes.value = ordenesRes.data?.data ?? ordenesRes.data;
  actividades.value = actividadesRes.data?.data ?? actividadesRes.data;
  matriz.value = matrizRes.data?.data ?? matrizRes.data;

  stats.ordenes = ordenes.value.length;
  stats.actividades = actividades.value.length;

  const actividadIdsConOrden = new Set(matriz.value.map((m) => m.pm_clase_actividad_id));
  stats.actividadesSinOrden = actividades.value.filter((a) => !actividadIdsConOrden.has(a.id)).length;

  const ordenMap = new Map(ordenes.value.map((o) => [o.id, o.nombre]));
  const actividadMap = new Map(actividades.value.map((a) => [a.id, a.nombre]));

  const grouped = new Map();
  matriz.value.forEach((m) => {
    const actividadId = m.pm_clase_actividad_id;
    const actividadNombre = actividadMap.get(actividadId) || '—';
    const ordenNombre = ordenMap.get(m.pm_clase_orden_id) || '—';

    if (!grouped.has(actividadId)) {
      grouped.set(actividadId, {
        actividadId,
        actividadNombre,
        ordenes: new Set(),
        ordenIds: new Set(),
        activo: true,
      });
    }

    const row = grouped.get(actividadId);
    row.ordenes.add(ordenNombre);
    row.ordenIds.add(m.pm_clase_orden_id);
    if (!m.activo) row.activo = false;
  });

  // Include actividades sin orden
  actividades.value.forEach((a) => {
    if (!grouped.has(a.id)) {
      grouped.set(a.id, {
        actividadId: a.id,
        actividadNombre: a.nombre,
        ordenes: new Set(),
        ordenIds: new Set(),
        activo: false,
      });
    }
  });

  matrizRows.value = Array.from(grouped.values()).map((r) => ({
    actividadId: r.actividadId,
    actividadNombre: r.actividadNombre,
    ordenes: Array.from(r.ordenes),
    ordenIds: Array.from(r.ordenIds),
    activo: r.activo,
  }));

  matrizItems.value = matriz.value.map((item) => ({
    id: item.id,
    pm_clase_orden_id: item.pm_clase_orden_id,
    pm_clase_actividad_id: item.pm_clase_actividad_id,
    ordenNombre: ordenMap.get(item.pm_clase_orden_id) || '—',
    actividadNombre: actividadMap.get(item.pm_clase_actividad_id) || '—',
    activo: item.activo,
    in_use: item.in_use,
  }));
};

const selectedOrdenInactive = computed(() => {
  const selected = ordenes.value.find((o) => String(o.id) === String(matrizForm.pm_clase_orden_id));
  return selected ? !selected.activo : false;
});

const selectedActividadInactive = computed(() => {
  const selected = actividades.value.find((a) => String(a.id) === String(matrizForm.pm_clase_actividad_id));
  return selected ? !selected.activo : false;
});

const filteredMatrizRows = computed(() => {
  const ordenId = Number(filterOrdenId.value);
  const actividadId = Number(filterActividadId.value);
  return matrizRows.value.filter((row) => {
    const matchActividad = !actividadId || row.actividadId === actividadId;
    const matchOrden = !ordenId || row.ordenIds.includes(ordenId);
    const matchEstado =
      !resumenStatus.value ||
      (resumenStatus.value === 'activo' && row.activo) ||
      (resumenStatus.value === 'pendiente' && !row.activo);
    return matchActividad && matchOrden && matchEstado;
  });
});

const filteredMatrizItems = computed(() => {
  const ordenId = Number(filterOrdenId.value);
  const actividadId = Number(filterActividadId.value);
  return matrizItems.value.filter((item) => {
    const matchOrden = !ordenId || item.pm_clase_orden_id === ordenId;
    const matchActividad = !actividadId || item.pm_clase_actividad_id === actividadId;
    const matchEstado =
      !matrizStatus.value ||
      (matrizStatus.value === 'activo' && item.activo) ||
      (matrizStatus.value === 'inactivo' && !item.activo);
    return matchOrden && matchActividad && matchEstado;
  });
});

const filteredOrdenes = computed(() => {
  const query = ordenesQuery.value.trim().toLowerCase();
  return ordenes.value.filter((orden) => {
    const matchQuery =
      !query ||
      String(orden.nombre || '').toLowerCase().includes(query) ||
      String(orden.descripcion || '').toLowerCase().includes(query);
    const matchStatus =
      !ordenesStatus.value ||
      (ordenesStatus.value === 'activo' && orden.activo) ||
      (ordenesStatus.value === 'inactivo' && !orden.activo);
    return matchQuery && matchStatus;
  });
});

const filteredActividades = computed(() => {
  const query = actividadesQuery.value.trim().toLowerCase();
  return actividades.value.filter((actividad) => {
    const matchQuery =
      !query ||
      String(actividad.nombre || '').toLowerCase().includes(query) ||
      String(actividad.descripcion || '').toLowerCase().includes(query);
    const matchStatus =
      !actividadesStatus.value ||
      (actividadesStatus.value === 'activo' && actividad.activo) ||
      (actividadesStatus.value === 'inactivo' && !actividad.activo);
    return matchQuery && matchStatus;
  });
});

const paginate = (items, page) => {
  const start = (page - 1) * perPage.value;
  return items.slice(start, start + perPage.value);
};

const totalPages = (items) => Math.max(1, Math.ceil(items.length / perPage.value));

const ordenesTotalPages = computed(() => totalPages(filteredOrdenes.value));
const actividadesTotalPages = computed(() => totalPages(filteredActividades.value));
const resumenTotalPages = computed(() => totalPages(filteredMatrizRows.value));
const matrizTotalPages = computed(() => totalPages(filteredMatrizItems.value));

const paginatedOrdenes = computed(() => paginate(filteredOrdenes.value, ordenesPage.value));
const paginatedActividades = computed(() => paginate(filteredActividades.value, actividadesPage.value));
const paginatedResumenRows = computed(() => paginate(filteredMatrizRows.value, resumenPage.value));
const paginatedMatrizItems = computed(() => paginate(filteredMatrizItems.value, matrizPage.value));

const rangeInfo = (items, page) => {
  if (!items.length) return { start: 0, end: 0 };
  const start = (page - 1) * perPage.value + 1;
  const end = Math.min(page * perPage.value, items.length);
  return { start, end };
};

const ordenesRange = computed(() => rangeInfo(filteredOrdenes.value, ordenesPage.value));
const actividadesRange = computed(() => rangeInfo(filteredActividades.value, actividadesPage.value));
const resumenRange = computed(() => rangeInfo(filteredMatrizRows.value, resumenPage.value));
const matrizRange = computed(() => rangeInfo(filteredMatrizItems.value, matrizPage.value));

watch([ordenesQuery, ordenesStatus], () => {
  ordenesPage.value = 1;
});

watch([actividadesQuery, actividadesStatus], () => {
  actividadesPage.value = 1;
});

watch(filterOrdenId, () => {
  resumenPage.value = 1;
  matrizPage.value = 1;
});

watch(filterActividadId, () => {
  resumenPage.value = 1;
  matrizPage.value = 1;
});

watch(resumenStatus, () => {
  resumenPage.value = 1;
});

watch(matrizStatus, () => {
  matrizPage.value = 1;
});

watch(ordenesTotalPages, (value) => {
  if (ordenesPage.value > value) ordenesPage.value = value;
});

watch(actividadesTotalPages, (value) => {
  if (actividadesPage.value > value) actividadesPage.value = value;
});

watch(resumenTotalPages, (value) => {
  if (resumenPage.value > value) resumenPage.value = value;
});

watch(matrizTotalPages, (value) => {
  if (matrizPage.value > value) matrizPage.value = value;
});

const clearFilters = () => {
  ordenesQuery.value = '';
  ordenesStatus.value = '';
  actividadesQuery.value = '';
  actividadesStatus.value = '';
  filterOrdenId.value = '';
  filterActividadId.value = '';
  resumenStatus.value = '';
  matrizStatus.value = '';
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 2600);
};

const clearErrors = (target) => {
  Object.keys(target).forEach((key) => (target[key] = ''));
};

const openOrdenCreate = () => {
  clearErrors(ordenErrors);
  editingOrdenId.value = null;
  ordenForm.nombre = '';
  ordenForm.descripcion = '';
  ordenForm.activo = 1;
  showOrdenModal.value = true;
};

const openOrdenEdit = (orden) => {
  clearErrors(ordenErrors);
  editingOrdenId.value = orden.id;
  ordenForm.nombre = orden.nombre || '';
  ordenForm.descripcion = orden.descripcion || '';
  ordenForm.activo = orden.activo ? 1 : 0;
  showOrdenModal.value = true;
};

const closeOrdenModal = () => {
  showOrdenModal.value = false;
};

const saveOrden = async () => {
  clearErrors(ordenErrors);
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingOrdenId.value) {
      await window.axios.put(`/api/v1/admin/pm/ordenes/${editingOrdenId.value}`, ordenForm);
      showToast('Clase de orden actualizada.');
    } else {
      await window.axios.post('/api/v1/admin/pm/ordenes', ordenForm);
      showToast('Clase de orden creada.');
    }
    await loadData();
    showOrdenModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      ordenErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const deleteOrden = async (orden) => {
  if (orden.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar la clase de orden "${orden.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/pm/ordenes/${orden.id}`);
    await loadData();
    showToast('Clase de orden eliminada.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

const openActividadCreate = () => {
  clearErrors(actividadErrors);
  editingActividadId.value = null;
  actividadForm.nombre = '';
  actividadForm.descripcion = '';
  actividadForm.activo = 1;
  showActividadModal.value = true;
};

const openActividadEdit = (actividad) => {
  clearErrors(actividadErrors);
  editingActividadId.value = actividad.id;
  actividadForm.nombre = actividad.nombre || '';
  actividadForm.descripcion = actividad.descripcion || '';
  actividadForm.activo = actividad.activo ? 1 : 0;
  showActividadModal.value = true;
};

const closeActividadModal = () => {
  showActividadModal.value = false;
};

const saveActividad = async () => {
  clearErrors(actividadErrors);
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    if (editingActividadId.value) {
      await window.axios.put(`/api/v1/admin/pm/actividades/${editingActividadId.value}`, actividadForm);
      showToast('Clase de actividad actualizada.');
    } else {
      await window.axios.post('/api/v1/admin/pm/actividades', actividadForm);
      showToast('Clase de actividad creada.');
    }
    await loadData();
    showActividadModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      actividadErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const deleteActividad = async (actividad) => {
  if (actividad.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar la clase de actividad "${actividad.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/pm/actividades/${actividad.id}`);
    await loadData();
    showToast('Clase de actividad eliminada.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

const openMatrizCreate = () => {
  clearErrors(matrizErrors);
  editingMatrizId.value = null;
  matrizForm.pm_clase_orden_id = '';
  matrizForm.pm_clase_actividad_id = '';
  matrizForm.activo = 1;
  showMatrizModal.value = true;
};

const openMatrizEdit = (item) => {
  clearErrors(matrizErrors);
  editingMatrizId.value = item.id;
  matrizForm.pm_clase_orden_id = item.pm_clase_orden_id;
  matrizForm.pm_clase_actividad_id = item.pm_clase_actividad_id;
  matrizForm.activo = item.activo ? 1 : 0;
  showMatrizModal.value = true;
};

const closeMatrizModal = () => {
  showMatrizModal.value = false;
};

const saveMatriz = async () => {
  clearErrors(matrizErrors);
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const ordenId = Number(matrizForm.pm_clase_orden_id);
    const actividadId = Number(matrizForm.pm_clase_actividad_id);
    if (!ordenId) {
      matrizErrors.pm_clase_orden_id = 'Selecciona una clase de orden';
      return;
    }
    if (!actividadId) {
      matrizErrors.pm_clase_actividad_id = 'Selecciona una clase de actividad';
      return;
    }

    const orden = ordenes.value.find((o) => o.id === ordenId);
    const actividad = actividades.value.find((a) => a.id === actividadId);
    if (orden && !orden.activo) {
      showToast('No se puede relacionar una clase de orden inactiva.', 'error');
      return;
    }
    if (actividad && !actividad.activo) {
      showToast('No se puede relacionar una clase de actividad inactiva.', 'error');
      return;
    }

    const duplicate = matrizItems.value.some(
      (item) =>
        item.pm_clase_orden_id === ordenId &&
        item.pm_clase_actividad_id === actividadId &&
        item.id !== editingMatrizId.value
    );

    if (duplicate) {
      showToast('La combinación ya existe.', 'error');
      return;
    }

    const payload = {
      pm_clase_orden_id: ordenId,
      pm_clase_actividad_id: actividadId,
      activo: matrizForm.activo ? 1 : 0,
    };

    if (editingMatrizId.value) {
      await window.axios.put(`/api/v1/admin/pm/matriz/${editingMatrizId.value}`, payload);
      showToast('Combinación actualizada.');
    } else {
      await window.axios.post('/api/v1/admin/pm/matriz', payload);
      showToast('Combinación creada.');
    }
    await loadData();
    showMatrizModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      matrizErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const deleteMatriz = async (item) => {
  if (item.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar la combinacion "${item.ordenNombre} + ${item.actividadNombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/pm/matriz/${item.id}`);
    await loadData();
    showToast('Combinación eliminada.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

onMounted(loadData);
</script>
