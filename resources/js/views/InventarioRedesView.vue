<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Inventario NMS/EMS</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Inventario de Redes</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Catálogo de redes e inventario de elementos.</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openRedCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nueva Red</span>
          </button>
          <button
            class="inline-flex items-center gap-2 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9] px-4 py-2.5 rounded-lg font-bold text-sm transition-all hover:bg-slate-50 dark:hover:bg-border-dark"
            @click="openDominioCreate"
          >
            <span class="material-symbols-outlined">category</span>
            <span>Nuevo Dominio</span>
          </button>
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openElementoCreate"
          >
            <span class="material-symbols-outlined">add</span>
            <span>Nuevo Elemento</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="xl:col-span-1 bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Redes</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Catálogo de redes de comunicaciones.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ filteredRedes.length }} registros
            </span>
          </div>
          <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
            <input
              v-model="redesQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar red..."
            />
            <select v-model="redesStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todas</option>
              <option value="activa">Activas</option>
              <option value="inactiva">Inactivas</option>
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
                  <th class="px-6 py-4">Red</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="red in paginatedRedes" :key="red.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ red.nombre }}</div>
                    <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ red.codigo }}</div>
                    <div v-if="red.dominios && red.dominios.length" class="mt-2 flex flex-wrap gap-1">
                      <span
                        v-for="dominio in red.dominios"
                        :key="dominio.id"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9]"
                      >
                        {{ dominio.nombre }}
                      </span>
                    </div>
                    <div v-else class="mt-2 text-[11px] text-slate-400 dark:text-[#92adc9]">Sin dominios</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="red.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ red.activo ? 'Activa' : 'Inactiva' }}
                    </span>
                    <span v-if="red.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openRedEdit(red)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="red.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="red.in_use"
                        :title="red.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar'"
                        @click="deleteRed(red)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredRedes.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="3">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="redesPage"
            :total-pages="redesTotalPages"
            :range-start="redesRange.start"
            :range-end="redesRange.end"
            :total-items="filteredRedes.length"
            @update:page="redesPage = $event"
          />
        </div>

        <div class="xl:col-span-1 bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Dominios</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Catálogo de dominios tecnológicos.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ filteredDominios.length }} registros
            </span>
          </div>
          <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
            <input
              v-model="dominiosQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar dominio..."
            />
            <select v-model="dominiosStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
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
                  <th class="px-6 py-4">Dominio</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="dominio in paginatedDominios" :key="dominio.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ dominio.nombre }}</div>
                    <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ dominio.codigo }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="dominio.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ dominio.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <span v-if="dominio.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openDominioEdit(dominio)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="dominio.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="dominio.in_use"
                        :title="dominio.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar'"
                        @click="deleteDominio(dominio)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredDominios.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="3">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="dominiosPage"
            :total-pages="dominiosTotalPages"
            :range-start="dominiosRange.start"
            :range-end="dominiosRange.end"
            :total-items="filteredDominios.length"
            @update:page="dominiosPage = $event"
          />
        </div>

        <div class="xl:col-span-3 bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Elementos</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Filtros por red, tipo, estado u origen.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ filteredElementos.length }} registros
            </span>
          </div>
          <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
            <input
              v-model="elementosQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar elemento..."
            />
            <select v-model="elementosRed" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todas las redes</option>
              <option v-for="red in redes" :key="red.id" :value="red.id">{{ red.nombre }}</option>
            </select>
            <select v-model="elementosTipo" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos los tipos</option>
              <option value="nodo">Nodo</option>
              <option value="enlace">Enlace</option>
              <option value="servicio">Servicio</option>
              <option value="tunel">Túnel</option>
              <option value="trail">Trail</option>
            </select>
            <select v-model="elementosEstado" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos los estados</option>
              <option value="activo">Activo</option>
              <option value="baja">Baja</option>
              <option value="planificado">Planificado</option>
              <option value="desconocido">Desconocido</option>
            </select>
            <select v-model="elementosOrigen" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos los orígenes</option>
              <option value="manual">Manual</option>
              <option value="csv">CSV</option>
              <option value="integracion">Integración</option>
            </select>
            <select v-model="elementosUso" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos</option>
              <option value="usado">Usados</option>
              <option value="no_usado">No usados</option>
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
                  <th class="px-6 py-4">Elemento</th>
                  <th class="px-6 py-4">Tipo</th>
                  <th class="px-6 py-4">Red</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4">Origen</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="el in paginatedElementos" :key="el.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-2">
                      <div>
                        <div class="text-sm font-medium">{{ el.nombre || el.codigo }}</div>
                        <div class="text-xs text-slate-500 dark:text-[#92adc9]">
                          {{ el.codigo }}
                        </div>
                      </div>
                      <button
                        v-if="hasExtensionTable(el.tipo)"
                        type="button"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border border-primary/20 text-primary bg-primary/10 cursor-help"
                        @mouseenter="openExtensionTooltip(el, $event)"
                        @focus="openExtensionTooltip(el, $event)"
                        @mouseleave="scheduleHideExtensionTooltip"
                        @blur="scheduleHideExtensionTooltip"
                      >
                        <span class="material-symbols-outlined text-xs">info</span>
                        <span>Detalle</span>
                      </button>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ el.tipo || '-' }}</td>
                  <td class="px-6 py-4 text-sm">
                    <div>{{ el.red?.nombre || redById(el.red_id)?.nombre || '-' }}</div>
                    <div v-if="el.red?.dominios?.length" class="mt-1 flex flex-wrap gap-1">
                      <span
                        v-for="dominio in el.red.dominios"
                        :key="dominio.id"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9]"
                      >
                        {{ dominio.nombre }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="estadoClass(el.estado)"
                    >
                      {{ formatEstado(el.estado) }}
                    </span>
                    <span v-if="el.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ formatOrigen(el.origen) }}</td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openElementoEdit(el)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="el.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="el.in_use"
                        :title="el.in_use ? 'No se puede eliminar porque está en uso' : 'Eliminar'"
                        @click="deleteElemento(el)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredElementos.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="6">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="elementosPage"
            :total-pages="elementosTotalPages"
            :range-start="elementosRange.start"
            :range-end="elementosRange.end"
            :total-items="filteredElementos.length"
            @update:page="elementosPage = $event"
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

    <div v-if="showRedModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingRedId ? 'Editar red' : 'Nueva red' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: código y nombre.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeRedModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="redForm.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="redErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="redErrors.codigo" class="mt-1 text-xs text-red-500">{{ redErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="redForm.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="redErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="redErrors.nombre" class="mt-1 text-xs text-red-500">{{ redErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Dominios</label>
              <div class="mt-2 flex flex-wrap gap-2">
                <label
                  v-for="dominio in dominios"
                  :key="dominio.id"
                  class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-sm cursor-pointer"
                  :class="!dominio.activo && !redForm.dominio_ids.includes(dominio.id) ? 'opacity-60 cursor-not-allowed' : ''"
                >
                  <input
                    type="checkbox"
                    class="rounded text-primary focus:ring-primary"
                    :value="dominio.id"
                    v-model="redForm.dominio_ids"
                    :disabled="!dominio.activo && !redForm.dominio_ids.includes(dominio.id)"
                  />
                  <span>{{ dominio.nombre }}</span>
                </label>
              </div>
              <p v-if="dominios.length === 0" class="mt-2 text-xs text-amber-500">
                No hay dominios disponibles. Agrega un dominio primero.
              </p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
              <select v-model="redForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activa</option>
                <option :value="0">Inactiva</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <input v-model="redForm.descripcion" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeRedModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveRed">
            {{ editingRedId ? 'Guardar cambios' : 'Crear red' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDominioModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingDominioId ? 'Editar dominio' : 'Nuevo dominio' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: código y nombre.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeDominioModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="dominioForm.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="dominioErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="dominioErrors.codigo" class="mt-1 text-xs text-red-500">{{ dominioErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="dominioForm.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="dominioErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="dominioErrors.nombre" class="mt-1 text-xs text-red-500">{{ dominioErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Orden</label>
              <input v-model="dominioForm.orden" type="number" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
              <select v-model="dominioForm.activo" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Descripción</label>
            <input v-model="dominioForm.descripcion" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeDominioModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveDominio">
            {{ editingDominioId ? 'Guardar cambios' : 'Crear dominio' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showElementoModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-3xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingElementoId ? 'Editar elemento' : 'Nuevo elemento' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">
              {{ editingElementoId ? 'El tipo no se puede cambiar en edición para preservar integridad de datos.' : 'Campos obligatorios: red, tipo y código.' }}
            </p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeElementoModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Red</label>
              <select
                v-model="elementoForm.red_id"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="elementoErrors.red_id ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              >
                <option value="">Selecciona una red</option>
                <option v-for="red in redes" :key="red.id" :value="red.id">{{ red.nombre }}</option>
              </select>
              <p v-if="elementoErrors.red_id" class="mt-1 text-xs text-red-500">{{ elementoErrors.red_id }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Tipo</label>
              <select
                v-model="elementoForm.tipo"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="[
                  elementoErrors.tipo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark',
                  editingElementoId ? 'opacity-70 cursor-not-allowed' : '',
                ]"
                :disabled="Boolean(editingElementoId)"
              >
                <option value="">Selecciona un tipo</option>
                <option value="nodo">Nodo</option>
                <option value="enlace">Enlace</option>
                <option value="servicio">Servicio</option>
                <option value="tunel">Túnel</option>
                <option value="trail">Trail</option>
              </select>
              <p v-if="elementoErrors.tipo" class="mt-1 text-xs text-red-500">{{ elementoErrors.tipo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Código</label>
              <input
                v-model="elementoForm.codigo"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="elementoErrors.codigo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="elementoErrors.codigo" class="mt-1 text-xs text-red-500">{{ elementoErrors.codigo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input v-model="elementoForm.nombre" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estado</label>
              <select v-model="elementoForm.estado" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="activo">Activo</option>
                <option value="baja">Baja</option>
                <option value="planificado">Planificado</option>
                <option value="desconocido">Desconocido</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Observaciones</label>
            <input v-model="elementoForm.observaciones" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
          </div>
          <div v-if="editingElementoId && elementoForm.tipo === 'nodo'" class="rounded-xl border border-slate-200 dark:border-border-dark overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 dark:bg-border-dark/30 border-b border-slate-200 dark:border-border-dark">
              <h5 class="text-sm font-bold text-slate-900 dark:text-white">Detalle de nodo</h5>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">Datos de la tabla de extensión `inv_detalle_nodos` (solo lectura).</p>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">NE ID</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.ne_id) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">NE DBID</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.ne_dbid) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">DN Externo</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white break-all">{{ displayDetalleNodo(detalleNodoForm.dn_externo) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Native Name</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white break-all">{{ displayDetalleNodo(detalleNodoForm.native_name) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">User Label</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white break-all">{{ displayDetalleNodo(detalleNodoForm.user_label) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre Producto</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.nombre_producto) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Tipo Equipo</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.tipo_equipo) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Versión ME</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.version_me) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Dirección Red</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.direccion_red) }}</p>
              </div>
              <div class="rounded-lg border border-slate-200 dark:border-border-dark px-3 py-2 bg-white dark:bg-surface-dark">
                <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre Grupo</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ displayDetalleNodo(detalleNodoForm.nombre_grupo) }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeElementoModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveElemento">
            {{ editingElementoId ? 'Guardar cambios' : 'Crear elemento' }}
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

    <div
      v-if="extensionTooltip.visible"
      ref="extensionTooltipRef"
      class="fixed z-[70] w-80 max-w-[calc(100vw-1rem)] rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-[#16222e] p-3 shadow-xl"
      :style="{ top: `${extensionTooltip.top}px`, left: `${extensionTooltip.left}px` }"
      @mouseenter="cancelHideExtensionTooltip"
      @mouseleave="scheduleHideExtensionTooltip"
    >
      <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">
        {{ extensionTooltip.table }}
      </p>
      <div v-if="extensionTooltip.rows.length" class="mt-2 space-y-1.5">
        <div
          v-for="item in extensionTooltip.rows"
          :key="`${extensionTooltip.elementoId}-${item.key}`"
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
  </DashboardLayout>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import PaginationBar from '../components/PaginationBar.vue';
import { fetchConfiguracionSistema } from '../api/configuracion';

const redes = ref([]);
const dominios = ref([]);
const elementos = ref([]);
const redesQuery = ref('');
const redesStatus = ref('');
const dominiosQuery = ref('');
const dominiosStatus = ref('');
const elementosQuery = ref('');
const elementosRed = ref('');
const elementosTipo = ref('');
const elementosEstado = ref('');
const elementosOrigen = ref('');
const elementosUso = ref('');
const perPage = ref(20);
const purgeRunning = ref(false);
const redesPage = ref(1);
const dominiosPage = ref(1);
const elementosPage = ref(1);
const showRedModal = ref(false);
const showDominioModal = ref(false);
const showElementoModal = ref(false);
const editingRedId = ref(null);
const editingDominioId = ref(null);
const editingElementoId = ref(null);
const redErrors = reactive({});
const dominioErrors = reactive({});
const elementoErrors = reactive({});
const redForm = reactive({
  codigo: '',
  nombre: '',
  descripcion: '',
  activo: 1,
  dominio_ids: [],
});
const dominioForm = reactive({
  codigo: '',
  nombre: '',
  descripcion: '',
  orden: null,
  activo: 1,
});
const elementoForm = reactive({
  red_id: '',
  tipo: '',
  codigo: '',
  nombre: '',
  estado: 'activo',
  observaciones: '',
});
const detalleNodoForm = reactive({
  ne_id: '',
  ne_dbid: '',
  dn_externo: '',
  native_name: '',
  user_label: '',
  nombre_producto: '',
  tipo_equipo: '',
  version_me: '',
  direccion_red: '',
  nombre_grupo: '',
});
const toast = reactive({
  visible: false,
  message: '',
  type: 'success',
});
const extensionTooltipRef = ref(null);
const extensionTooltip = reactive({
  visible: false,
  elementoId: null,
  table: '',
  rows: [],
  top: 0,
  left: 0,
});
let extensionTooltipHideTimer = null;

const loadData = async () => {
  const config = await fetchConfiguracionSistema();
  const map = Object.fromEntries(config.map((item) => [item.clave, item.valor]));
  const per = Number(map['paginacion.default_per_page'] ?? 20);
  perPage.value = Number.isFinite(per) && per > 0 ? per : 20;

  const [redesRes, dominiosRes, elementosRes] = await Promise.all([
    window.axios.get('/api/v1/admin/inventario/redes'),
    window.axios.get('/api/v1/admin/inventario/dominios'),
    window.axios.get('/api/v1/admin/inventario/elementos'),
  ]);
  redes.value = redesRes.data?.data ?? redesRes.data;
  dominios.value = dominiosRes.data?.data ?? dominiosRes.data;
  elementos.value = elementosRes.data?.data ?? elementosRes.data;
};

const filteredRedes = computed(() => {
  const q = redesQuery.value.trim().toLowerCase();
  return redes.value.filter((red) => {
    const dominiosText = (red.dominios || [])
      .map((dominio) => `${dominio.nombre} ${dominio.codigo}`)
      .join(' ')
      .toLowerCase();
    const matchQuery =
      !q ||
      String(red.nombre || '').toLowerCase().includes(q) ||
      String(red.codigo || '').toLowerCase().includes(q) ||
      dominiosText.includes(q);
    const matchStatus =
      !redesStatus.value ||
      (redesStatus.value === 'activa' && red.activo) ||
      (redesStatus.value === 'inactiva' && !red.activo);
    return matchQuery && matchStatus;
  });
});

const filteredDominios = computed(() => {
  const q = dominiosQuery.value.trim().toLowerCase();
  return dominios.value.filter((dominio) => {
    const matchQuery =
      !q ||
      String(dominio.nombre || '').toLowerCase().includes(q) ||
      String(dominio.codigo || '').toLowerCase().includes(q);
    const matchStatus =
      !dominiosStatus.value ||
      (dominiosStatus.value === 'activo' && dominio.activo) ||
      (dominiosStatus.value === 'inactivo' && !dominio.activo);
    return matchQuery && matchStatus;
  });
});

const filteredElementos = computed(() => {
  const q = elementosQuery.value.trim().toLowerCase();
  return elementos.value.filter((el) => {
    const dominiosText = (el.red?.dominios || [])
      .map((dominio) => `${dominio.nombre} ${dominio.codigo}`)
      .join(' ')
      .toLowerCase();
    const matchQuery =
      !q ||
      String(el.nombre || '').toLowerCase().includes(q) ||
      String(el.codigo || '').toLowerCase().includes(q) ||
      String(el.tipo || '').toLowerCase().includes(q) ||
      dominiosText.includes(q);
    const matchRed = !elementosRed.value || String(el.red_id) === String(elementosRed.value);
    const matchTipo = !elementosTipo.value || String(el.tipo) === String(elementosTipo.value);
    const matchEstado = !elementosEstado.value || String(el.estado) === String(elementosEstado.value);
    const matchOrigen = !elementosOrigen.value || String(el.origen) === String(elementosOrigen.value);
    const matchUso =
      !elementosUso.value ||
      (elementosUso.value === 'usado' && Boolean(el.in_use)) ||
      (elementosUso.value === 'no_usado' && !Boolean(el.in_use));
    return matchQuery && matchRed && matchTipo && matchEstado && matchOrigen && matchUso;
  });
});

const redesTotalPages = computed(() => Math.max(1, Math.ceil(filteredRedes.value.length / perPage.value)));
const dominiosTotalPages = computed(() => Math.max(1, Math.ceil(filteredDominios.value.length / perPage.value)));
const elementosTotalPages = computed(() => Math.max(1, Math.ceil(filteredElementos.value.length / perPage.value)));

const paginatedRedes = computed(() => {
  const start = (redesPage.value - 1) * perPage.value;
  return filteredRedes.value.slice(start, start + perPage.value);
});

const paginatedDominios = computed(() => {
  const start = (dominiosPage.value - 1) * perPage.value;
  return filteredDominios.value.slice(start, start + perPage.value);
});

const paginatedElementos = computed(() => {
  const start = (elementosPage.value - 1) * perPage.value;
  return filteredElementos.value.slice(start, start + perPage.value);
});

const redesRange = computed(() => {
  if (!filteredRedes.value.length) return { start: 0, end: 0 };
  const start = (redesPage.value - 1) * perPage.value + 1;
  const end = Math.min(redesPage.value * perPage.value, filteredRedes.value.length);
  return { start, end };
});

const dominiosRange = computed(() => {
  if (!filteredDominios.value.length) return { start: 0, end: 0 };
  const start = (dominiosPage.value - 1) * perPage.value + 1;
  const end = Math.min(dominiosPage.value * perPage.value, filteredDominios.value.length);
  return { start, end };
});

const elementosRange = computed(() => {
  if (!filteredElementos.value.length) return { start: 0, end: 0 };
  const start = (elementosPage.value - 1) * perPage.value + 1;
  const end = Math.min(elementosPage.value * perPage.value, filteredElementos.value.length);
  return { start, end };
});

watch([redesQuery, redesStatus], () => {
  redesPage.value = 1;
});

watch([dominiosQuery, dominiosStatus], () => {
  dominiosPage.value = 1;
});

watch([elementosQuery, elementosRed, elementosTipo, elementosEstado, elementosOrigen, elementosUso], () => {
  elementosPage.value = 1;
});

watch(redesTotalPages, (value) => {
  if (redesPage.value > value) redesPage.value = value;
});

watch(dominiosTotalPages, (value) => {
  if (dominiosPage.value > value) dominiosPage.value = value;
});

watch(elementosTotalPages, (value) => {
  if (elementosPage.value > value) elementosPage.value = value;
});

const clearFilters = () => {
  redesQuery.value = '';
  redesStatus.value = '';
  dominiosQuery.value = '';
  dominiosStatus.value = '';
  elementosQuery.value = '';
  elementosRed.value = '';
  elementosTipo.value = '';
  elementosEstado.value = '';
  elementosOrigen.value = '';
  elementosUso.value = '';
};

const redById = (id) => redes.value.find((r) => String(r.id) === String(id));

const formatEstado = (estado) => {
  const map = {
    activo: 'Activo',
    baja: 'Baja',
    planificado: 'Planificado',
    desconocido: 'Desconocido',
  };
  return map[estado] || estado || '-';
};

const estadoClass = (estado) => {
  switch (estado) {
    case 'activo':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    case 'baja':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
    case 'planificado':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
    default:
      return 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]';
  }
};

const formatOrigen = (origen) => {
  const map = {
    manual: 'Manual',
    csv: 'CSV',
    integracion: 'Integración',
  };
  return map[origen] || origen || '-';
};

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

const cancelHideExtensionTooltip = () => {
  if (extensionTooltipHideTimer !== null) {
    window.clearTimeout(extensionTooltipHideTimer);
    extensionTooltipHideTimer = null;
  }
};

const hideExtensionTooltip = () => {
  cancelHideExtensionTooltip();
  extensionTooltip.visible = false;
  extensionTooltip.elementoId = null;
  extensionTooltip.table = '';
  extensionTooltip.rows = [];
};

const scheduleHideExtensionTooltip = () => {
  cancelHideExtensionTooltip();
  extensionTooltipHideTimer = window.setTimeout(() => {
    hideExtensionTooltip();
  }, 120);
};

const positionExtensionTooltip = (triggerEl) => {
  const tooltipEl = extensionTooltipRef.value;
  if (!tooltipEl || !(triggerEl instanceof HTMLElement)) return;

  const margin = 8;
  const rect = triggerEl.getBoundingClientRect();
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const tooltipWidth = tooltipEl.offsetWidth || 320;
  const tooltipHeight = tooltipEl.offsetHeight || 180;

  let left = rect.left;
  if (left + tooltipWidth + margin > vw) left = vw - tooltipWidth - margin;
  if (left < margin) left = margin;

  let top = rect.bottom + margin;
  const canShowBelow = top + tooltipHeight + margin <= vh;
  if (!canShowBelow) {
    top = rect.top - tooltipHeight - margin;
  }
  if (top < margin) top = margin;

  extensionTooltip.left = Math.round(left);
  extensionTooltip.top = Math.round(top);
};

const openExtensionTooltip = (elemento, event) => {
  if (!hasExtensionTable(elemento?.tipo)) return;
  const triggerEl = event?.currentTarget;
  if (!(triggerEl instanceof HTMLElement)) return;

  cancelHideExtensionTooltip();
  extensionTooltip.visible = true;
  extensionTooltip.elementoId = elemento?.id ?? null;
  extensionTooltip.table = extensionTableName(elemento?.tipo) || '';
  extensionTooltip.rows = extensionTooltipRows(elemento);

  nextTick(() => {
    positionExtensionTooltip(triggerEl);
  });
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 2600);
};

const purgeNoUsados = async () => {
  if (purgeRunning.value) return;

  const confirm1 = window.confirm(
    'Acción de alto impacto: se eliminarán SOLO elementos de Inventario NMS/EMS no usados en bitácora. No se eliminarán redes, dominios ni datos de Ubicaciones/Equipos. ¿Deseas continuar?'
  );
  if (!confirm1) return;

  const confirm2 = window.confirm(
    'Confirmación 2/3: esta operación no se puede deshacer desde la app. ¿Seguro que deseas continuar?'
  );
  if (!confirm2) return;

  const confirm3 = window.confirm(
    'Confirmación 3/3: se eliminarán SOLO elementos no usados en bitácora. ¿Ejecutar depuración ahora?'
  );
  if (!confirm3) return;

  purgeRunning.value = true;
  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post('/api/v1/admin/depuracion/purgar-no-usados', {
      confirmaciones: 3,
      alcance: 'inventario',
    });
    const data = response.data?.data || {};
    await loadData();
    showToast(
      `Depuración completada. Elementos eliminados: ${data.inventario_elementos_eliminados || 0}.`
    );
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo ejecutar la depuración.', 'error');
  } finally {
    purgeRunning.value = false;
  }
};

const resetDetalleNodoForm = () => {
  detalleNodoForm.ne_id = '';
  detalleNodoForm.ne_dbid = '';
  detalleNodoForm.dn_externo = '';
  detalleNodoForm.native_name = '';
  detalleNodoForm.user_label = '';
  detalleNodoForm.nombre_producto = '';
  detalleNodoForm.tipo_equipo = '';
  detalleNodoForm.version_me = '';
  detalleNodoForm.direccion_red = '';
  detalleNodoForm.nombre_grupo = '';
};

const setDetalleNodoForm = (detalle) => {
  resetDetalleNodoForm();
  if (!detalle) return;
  detalleNodoForm.ne_id = detalle.ne_id ?? '';
  detalleNodoForm.ne_dbid = detalle.ne_dbid ?? '';
  detalleNodoForm.dn_externo = detalle.dn_externo ?? '';
  detalleNodoForm.native_name = detalle.native_name ?? '';
  detalleNodoForm.user_label = detalle.user_label ?? '';
  detalleNodoForm.nombre_producto = detalle.nombre_producto ?? '';
  detalleNodoForm.tipo_equipo = detalle.tipo_equipo ?? '';
  detalleNodoForm.version_me = detalle.version_me ?? '';
  detalleNodoForm.direccion_red = detalle.direccion_red ?? '';
  detalleNodoForm.nombre_grupo = detalle.nombre_grupo ?? '';
};

const displayDetalleNodo = (value) => {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return String(value);
};

const resetRedForm = () => {
  redForm.codigo = '';
  redForm.nombre = '';
  redForm.descripcion = '';
  redForm.activo = 1;
  redForm.dominio_ids = [];
  Object.keys(redErrors).forEach((key) => (redErrors[key] = ''));
};

const resetDominioForm = () => {
  dominioForm.codigo = '';
  dominioForm.nombre = '';
  dominioForm.descripcion = '';
  dominioForm.orden = null;
  dominioForm.activo = 1;
  Object.keys(dominioErrors).forEach((key) => (dominioErrors[key] = ''));
};

const resetElementoForm = () => {
  elementoForm.red_id = '';
  elementoForm.tipo = '';
  elementoForm.codigo = '';
  elementoForm.nombre = '';
  elementoForm.estado = 'activo';
  elementoForm.observaciones = '';
  resetDetalleNodoForm();
  Object.keys(elementoErrors).forEach((key) => (elementoErrors[key] = ''));
};

const openRedCreate = () => {
  resetRedForm();
  editingRedId.value = null;
  showRedModal.value = true;
};

const openDominioCreate = () => {
  resetDominioForm();
  editingDominioId.value = null;
  showDominioModal.value = true;
};

const openRedEdit = (red) => {
  resetRedForm();
  editingRedId.value = red.id;
  redForm.codigo = red.codigo || '';
  redForm.nombre = red.nombre || '';
  redForm.descripcion = red.descripcion || '';
  redForm.activo = red.activo ? 1 : 0;
  redForm.dominio_ids = (red.dominios || []).map((dominio) => dominio.id);
  showRedModal.value = true;
};

const openDominioEdit = (dominio) => {
  resetDominioForm();
  editingDominioId.value = dominio.id;
  dominioForm.codigo = dominio.codigo || '';
  dominioForm.nombre = dominio.nombre || '';
  dominioForm.descripcion = dominio.descripcion || '';
  dominioForm.orden = dominio.orden ?? null;
  dominioForm.activo = dominio.activo ? 1 : 0;
  showDominioModal.value = true;
};

const closeRedModal = () => {
  showRedModal.value = false;
};

const closeDominioModal = () => {
  showDominioModal.value = false;
};

const openElementoCreate = () => {
  resetElementoForm();
  editingElementoId.value = null;
  showElementoModal.value = true;
};

const openElementoEdit = (elemento) => {
  resetElementoForm();
  editingElementoId.value = elemento.id;
  elementoForm.red_id = elemento.red_id || '';
  elementoForm.tipo = elemento.tipo || '';
  elementoForm.codigo = elemento.codigo || '';
  elementoForm.nombre = elemento.nombre || '';
  elementoForm.estado = elemento.estado || 'activo';
  elementoForm.observaciones = elemento.observaciones || '';
  setDetalleNodoForm(elemento.detalle_nodo);
  showElementoModal.value = true;
};

const closeElementoModal = () => {
  showElementoModal.value = false;
};

const saveRed = async () => {
  Object.keys(redErrors).forEach((key) => (redErrors[key] = ''));
  try {
    if (editingRedId.value) {
      await window.axios.put(`/api/v1/admin/inventario/redes/${editingRedId.value}`, redForm);
      showToast('Red actualizada.');
    } else {
      await window.axios.post('/api/v1/admin/inventario/redes', redForm);
      showToast('Red creada.');
    }
    await loadData();
    showRedModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      redErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const saveDominio = async () => {
  Object.keys(dominioErrors).forEach((key) => (dominioErrors[key] = ''));
  try {
    if (editingDominioId.value) {
      await window.axios.put(`/api/v1/admin/inventario/dominios/${editingDominioId.value}`, dominioForm);
      showToast('Dominio actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/inventario/dominios', dominioForm);
      showToast('Dominio creado.');
    }
    await loadData();
    showDominioModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      dominioErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const saveElemento = async () => {
  Object.keys(elementoErrors).forEach((key) => (elementoErrors[key] = ''));
  try {
    const payload = {
      red_id: elementoForm.red_id,
      codigo: elementoForm.codigo,
      nombre: elementoForm.nombre,
      estado: elementoForm.estado,
      observaciones: elementoForm.observaciones,
    };

    if (editingElementoId.value) {
      await window.axios.put(`/api/v1/admin/inventario/elementos/${editingElementoId.value}`, payload);
      showToast('Elemento actualizado.');
    } else {
      await window.axios.post('/api/v1/admin/inventario/elementos', {
        ...payload,
        tipo: elementoForm.tipo,
      });
      showToast('Elemento creado.');
    }
    await loadData();
    showElementoModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      elementoErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar.', 'error');
    }
  }
};

const deleteRed = async (red) => {
  if (red.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar la red "${red.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/inventario/redes/${red.id}`);
    await loadData();
    showToast('Red eliminada.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

const deleteDominio = async (dominio) => {
  if (dominio.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar el dominio "${dominio.nombre}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/inventario/dominios/${dominio.id}`);
    await loadData();
    showToast('Dominio eliminado.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

const deleteElemento = async (elemento) => {
  if (elemento.in_use) {
    showToast('No se puede eliminar porque está en uso.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar el elemento "${elemento.nombre || elemento.codigo}"?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/inventario/elementos/${elemento.id}`);
    await loadData();
    showToast('Elemento eliminado.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar.', 'error');
  }
};

onMounted(() => {
  loadData();
  window.addEventListener('resize', hideExtensionTooltip);
  window.addEventListener('scroll', hideExtensionTooltip, true);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', hideExtensionTooltip);
  window.removeEventListener('scroll', hideExtensionTooltip, true);
  cancelHideExtensionTooltip();
});
</script>
