<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Reglas de Importación</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Importación de Inventario</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Define reglas por red y ejecuta importaciones manuales de archivos.</p>
        </div>
        <button
          class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
          @click="openReglaCreate"
        >
          <span class="material-symbols-outlined">add</span>
          <span>Nueva regla</span>
        </button>
      </div>

      <div class="grid grid-cols-1 gap-6">
        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h4 class="font-bold text-lg">Reglas</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9]">CRUD de reglas y mapeos por archivo.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ reglasFiltradas.length }} reglas
            </span>
          </div>

          <div class="px-6 py-4 flex flex-wrap items-center gap-2">
            <select v-model="reglasRedFiltro" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todas las redes</option>
              <option v-for="red in redes" :key="red.id" :value="String(red.id)">{{ red.nombre }}</option>
            </select>
            <input
              v-model="reglasQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar regla..."
            />
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">Regla</th>
                  <th class="px-6 py-4">Tipo</th>
                  <th class="px-6 py-4">Red</th>
                  <th class="px-6 py-4">Estado</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="regla in reglasPaginadas" :key="regla.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ regla.nombre }}</div>
                    <div class="text-xs text-slate-500 dark:text-[#92adc9]">
                      {{ regla.campos?.length || 0 }} campos
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm capitalize">{{ regla.tipo_elemento || '-' }}</td>
                  <td class="px-6 py-4 text-sm">{{ regla.red?.nombre || '-' }}</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="regla.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'">
                      {{ regla.activo ? 'Activa' : 'Inactiva' }}
                    </span>
                    <span v-if="regla.in_use" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                      En uso
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openReglaEdit(regla)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="regla.in_use ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="regla.in_use"
                        @click="deleteRegla(regla)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="reglasPaginadas.length === 0">
                  <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]">Sin reglas</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="reglasPage"
            :total-pages="reglasTotalPages"
            :range-start="reglasRange.start"
            :range-end="reglasRange.end"
            :total-items="reglasFiltradas.length"
            @update:page="reglasPage = $event"
          />
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark">
            <h4 class="font-bold text-lg">Ejecutar Importación</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Selecciona red, regla y archivo. La ejecución crea registro en Inventario NMS/EMS.</p>
          </div>

          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Red</label>
                <select v-model="importForm.red_id" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                  <option value="">Selecciona red</option>
                  <option v-for="red in redes" :key="red.id" :value="String(red.id)">{{ red.nombre }}</option>
                </select>
              </div>
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Regla</label>
                <select v-model="importForm.regla_id" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                  <option value="">Selecciona regla</option>
                  <option v-for="regla in reglasImportables" :key="regla.id" :value="String(regla.id)">
                    {{ regla.nombre }} ({{ regla.tipo_elemento }})
                  </option>
                </select>
              </div>
            </div>

            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Archivo</label>
              <input
                type="file"
                class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-border-dark dark:bg-border-dark/60 dark:text-slate-200 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary file:transition-colors hover:file:bg-primary/20 dark:file:bg-primary/20 dark:hover:file:bg-primary/30"
                @change="onFileChange"
                accept=".dat,.csv,.txt,.lst"
              />
              <p class="mt-2 text-xs text-slate-500 dark:text-[#92adc9]">
                <template v-if="importForm.archivo">
                  Seleccionado: {{ importForm.archivo.name }}
                </template>
                <template v-else>
                  Sin archivos seleccionados{{ archivoPatronHint }}
                </template>
              </p>
            </div>

            <div class="flex items-center justify-end">
              <button
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25 disabled:opacity-60"
                :disabled="runningImport"
                @click="runImport"
              >
                <span class="material-symbols-outlined">upload_file</span>
                <span>{{ runningImport ? 'Importando...' : 'Ejecutar Importación' }}</span>
              </button>
            </div>
          </div>

          <div class="border-t border-slate-200 dark:border-border-dark p-6">
            <h5 class="font-semibold text-sm mb-3">Últimas ejecuciones</h5>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                  <tr>
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2">Red / Regla</th>
                    <th class="px-3 py-2">Archivo</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2 text-right">Resultado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                  <tr v-for="item in importacionesPaginadas" :key="item.id">
                    <td class="px-3 py-2 text-xs">{{ formatDate(item.created_at) }}</td>
                    <td class="px-3 py-2 text-xs">{{ item.red?.nombre || '-' }}<br><span class="text-slate-500">{{ item.regla?.nombre || '-' }}</span></td>
                    <td class="px-3 py-2 text-xs">{{ item.archivo_nombre }}</td>
                    <td class="px-3 py-2 text-xs">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                        :class="estadoImportacionClass(item.estado)"
                      >
                        {{ estadoImportacionLabel(item.estado) }}
                      </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-right">
                      <div class="font-semibold text-slate-700 dark:text-slate-200">{{ resultadoImportacionPrincipal(item) }}</div>
                      <div class="text-[11px] text-slate-500 dark:text-[#92adc9]">{{ resultadoImportacionDetalle(item) }}</div>
                      <div class="mt-1 inline-flex flex-wrap items-center justify-end gap-1">
                        <button
                          v-if="Number(item.errores_count || 0) > 0"
                          class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/40 transition-colors"
                          @click="openErroresModal(item)"
                        >
                          Ver errores ({{ item.errores_count }})
                        </button>
                        <button
                          v-if="canRevertImportacion(item)"
                          class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/40 transition-colors disabled:opacity-60"
                          :disabled="revertingImportId === item.id"
                          @click="revertirImportacion(item)"
                        >
                          {{ revertingImportId === item.id ? 'Revirtiendo...' : 'Revertir' }}
                        </button>
                        <span
                          v-else-if="item.reversion_status === 'revertida'"
                          class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold text-slate-700 bg-slate-200 dark:bg-border-dark/60 dark:text-[#92adc9]"
                        >
                          Revertida
                        </span>
                        <span
                          v-else-if="item.reversion_status === 'sin_nuevos'"
                          class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold text-slate-700 bg-slate-200 dark:bg-border-dark/60 dark:text-[#92adc9]"
                        >
                          Sin nuevos
                        </span>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="importacionesPaginadas.length === 0">
                    <td colspan="5" class="px-3 py-3 text-center text-sm text-slate-500 dark:text-[#92adc9]">Sin importaciones aún</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <PaginationBar
              :page="importacionesPage"
              :total-pages="importacionesTotalPages"
              :range-start="importacionesRange.start"
              :range-end="importacionesRange.end"
              :total-items="importaciones.length"
              @update:page="importacionesPage = $event"
            />
          </div>
        </div>
      </div>
    </div>

    <div v-if="showReglaModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-5xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingReglaId ? 'Editar regla' : 'Nueva regla' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Define archivo, formato y mapeo de columnas.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeReglaModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="p-6 space-y-4 max-h-[75vh] overflow-auto">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Red</label>
              <select v-model="reglaForm.red_id" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="">Selecciona red</option>
                <option v-for="red in redes" :key="red.id" :value="String(red.id)">{{ red.nombre }}</option>
              </select>
              <p v-if="reglaErrors.red_id" class="mt-1 text-xs text-red-500">{{ reglaErrors.red_id }}</p>
            </div>

            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input v-model="reglaForm.nombre" type="text" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm" :class="reglaErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'" />
              <p v-if="reglaErrors.nombre" class="mt-1 text-xs text-red-500">{{ reglaErrors.nombre }}</p>
            </div>

            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Tipo elemento</label>
              <select v-model="reglaForm.tipo_elemento" class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm">
                <option value="nodo">Nodo</option>
                <option value="enlace">Enlace</option>
                <option value="tunel">Túnel</option>
                <option value="servicio">Servicio</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-3 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/60 dark:bg-border-dark/20 p-4">
              <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Archivo y formato</p>
              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Patrón de archivo</label>
                  <input v-model="reglaForm.archivo_patron" type="text" class="mt-2 w-full bg-white dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" placeholder="*datos.csv" />
                </div>
                <div>
                  <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Encoding</label>
                  <input v-model="reglaForm.encoding" type="text" class="mt-2 w-full bg-white dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" placeholder="utf-8" />
                </div>
              </div>
              <div class="mt-3 max-w-[220px]">
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Delimitador</label>
                <input v-model="reglaForm.delimitador" maxlength="1" type="text" class="mt-2 w-full bg-white dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm" />
              </div>
            </div>

            <div class="lg:col-span-2 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/60 dark:bg-border-dark/20 p-4">
              <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Opciones</p>
              <div class="mt-3 space-y-2">
                <label class="flex items-center justify-between text-sm rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-border-dark/50 px-3 py-2">
                  <span>Usa comillas</span>
                  <input v-model="reglaForm.usa_comillas" type="checkbox" />
                </label>
                <label class="flex items-center justify-between text-sm rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-border-dark/50 px-3 py-2">
                  <span>Tiene encabezado</span>
                  <input v-model="reglaForm.tiene_encabezado" type="checkbox" />
                </label>
                <label class="flex items-center justify-between text-sm rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-border-dark/50 px-3 py-2">
                  <span>Activa</span>
                  <input v-model="reglaForm.activo" type="checkbox" />
                </label>
              </div>
            </div>
          </div>

          <div class="border rounded-lg border-slate-200 dark:border-border-dark overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 dark:bg-border-dark/30 flex items-center justify-between">
              <h5 class="text-sm font-semibold">Mapeo de campos</h5>
              <span class="text-xs text-slate-500 dark:text-[#92adc9]">Campos destino definidos por tipo de elemento (para soportar múltiples NMS)</span>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9]">
                  <tr>
                    <th class="px-3 py-2">Columna fuente</th>
                    <th class="px-3 py-2">Campo destino</th>
                    <th class="px-3 py-2">Transformación</th>
                    <th class="px-3 py-2">Default</th>
                    <th class="px-3 py-2">Orden</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(campo, idx) in reglaForm.campos" :key="idx" class="border-t border-slate-100 dark:border-border-dark">
                    <td class="px-3 py-2"><input v-model="campo.columna_fuente" type="text" class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded px-2 py-1 text-xs" :placeholder="reglaForm.tiene_encabezado ? 'Nombre o índice' : 'Índice (1,2,3...)'" /></td>
                    <td class="px-3 py-2"><input :value="campo.campo_destino" type="text" class="w-full bg-slate-100 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded px-2 py-1 text-xs" readonly /></td>
                    <td class="px-3 py-2"><input v-model="campo.transformacion" type="text" class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded px-2 py-1 text-xs" placeholder="trim|upper|to_int" /></td>
                    <td class="px-3 py-2"><input v-model="campo.por_defecto" type="text" class="w-full bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded px-2 py-1 text-xs" /></td>
                    <td class="px-3 py-2"><input v-model.number="campo.orden" type="number" class="w-20 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded px-2 py-1 text-xs" /></td>
                  </tr>
                  <tr v-if="reglaForm.campos.length === 0">
                    <td colspan="5" class="px-3 py-3 text-center text-xs text-slate-500">No hay campos configurables para este tipo.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeReglaModal">Cancelar</button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveRegla">{{ editingReglaId ? 'Guardar cambios' : 'Crear regla' }}</button>
        </div>
      </div>
    </div>

    <div v-if="showErroresModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-5xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Errores de importación</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">
              {{ erroresImportacionContext?.archivo_nombre || '-' }} ·
              {{ erroresImportacionContext?.red?.nombre || '-' }} /
              {{ erroresImportacionContext?.regla?.nombre || '-' }}
            </p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeErroresModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="p-6 max-h-[70vh] overflow-auto">
          <div v-if="erroresLoading" class="text-sm text-slate-500 dark:text-[#92adc9]">Cargando errores...</div>

          <div v-else-if="erroresImportacion.length === 0" class="text-sm text-slate-500 dark:text-[#92adc9]">
            Esta importación no tiene detalle de errores.
          </div>

          <div v-else class="overflow-x-auto rounded-lg border border-slate-200 dark:border-border-dark">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                <tr>
                  <th class="px-3 py-2">Fila</th>
                  <th class="px-3 py-2">Campo</th>
                  <th class="px-3 py-2">Valor</th>
                  <th class="px-3 py-2">Mensaje</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="error in erroresImportacion" :key="error.id">
                  <td class="px-3 py-2 text-xs">{{ error.fila_numero ?? '-' }}</td>
                  <td class="px-3 py-2 text-xs">{{ error.campo || '-' }}</td>
                  <td class="px-3 py-2 text-xs font-mono max-w-[280px] truncate" :title="error.valor || ''">{{ error.valor || '-' }}</td>
                  <td class="px-3 py-2 text-xs">{{ error.mensaje || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p
            v-if="!erroresLoading && erroresImportacionTotal > erroresImportacion.length"
            class="mt-3 text-xs text-slate-500 dark:text-[#92adc9]"
          >
            Mostrando {{ erroresImportacion.length }} de {{ erroresImportacionTotal }} errores.
          </p>
        </div>

        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeErroresModal">
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="toast.visible"
      class="fixed bottom-6 right-6 z-50 rounded-lg px-4 py-3 text-sm font-semibold shadow-lg border"
      :class="toast.type === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
    >
      {{ toast.message }}
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import PaginationBar from '../components/PaginationBar.vue';
import { fetchConfiguracionSistema } from '../api/configuracion';

const redes = ref([]);
const reglas = ref([]);
const importaciones = ref([]);
const reglasQuery = ref('');
const reglasRedFiltro = ref('');
const reglasPerPage = ref(20);
const reglasPage = ref(1);
const importacionesPerPage = ref(20);
const importacionesPage = ref(1);
const revertingImportId = ref(null);
const showErroresModal = ref(false);
const erroresLoading = ref(false);
const erroresImportacion = ref([]);
const erroresImportacionTotal = ref(0);
const erroresImportacionContext = ref(null);
const showReglaModal = ref(false);
const editingReglaId = ref(null);
const runningImport = ref(false);

const reglaErrors = reactive({});
const DESTINOS_POR_TIPO = Object.freeze({
  nodo: [
    { destino: 'codigo', fuente: '' },
    { destino: 'nombre', fuente: '' },
    { destino: 'observaciones', fuente: '' },
    { destino: 'ne_id', fuente: '' },
    { destino: 'ne_dbid', fuente: '' },
    { destino: 'dn_externo', fuente: '' },
    { destino: 'native_name', fuente: '' },
    { destino: 'user_label', fuente: '' },
    { destino: 'nombre_producto', fuente: '' },
    { destino: 'tipo_equipo', fuente: '' },
    { destino: 'version_me', fuente: '' },
    { destino: 'direccion_red', fuente: '' },
    { destino: 'nombre_grupo', fuente: '' },
  ],
  enlace: [
    { destino: 'codigo', fuente: '' },
    { destino: 'nombre', fuente: '' },
    { destino: 'observaciones', fuente: '' },
    { destino: 'instancia_enlace_id', fuente: '' },
    { destino: 'motlink_label', fuente: '' },
    { destino: 'trail_id', fuente: '' },
    { destino: 'nodo_a_ne_id', fuente: '' },
    { destino: 'nodo_z_ne_id', fuente: '' },
  ],
  tunel: [
    { destino: 'codigo', fuente: '' },
    { destino: 'nombre', fuente: '' },
    { destino: 'observaciones', fuente: '' },
    { destino: 'instancia_tunel_id', fuente: '' },
    { destino: 'user_label', fuente: '' },
    { destino: 'cliente', fuente: '' },
    { destino: 'tipo_tunel', fuente: '' },
    { destino: 'ethvpn_id', fuente: '' },
  ],
  servicio: [
    { destino: 'codigo', fuente: '' },
    { destino: 'nombre', fuente: '' },
    { destino: 'observaciones', fuente: '' },
    { destino: 'instancia_servicio_id', fuente: '' },
    { destino: 'user_label', fuente: '' },
    { destino: 'cliente', fuente: '' },
    { destino: 'tipo_servicio', fuente: '' },
    { destino: 'ethvpn_id', fuente: '' },
  ],
});

const reglaForm = reactive({
  red_id: '',
  nombre: '',
  tipo_elemento: 'nodo',
  tabla_destino: 'inv_elementos_redes',
  archivo_patron: '',
  delimitador: ',',
  usa_comillas: true,
  tiene_encabezado: false,
  encoding: 'utf-8',
  activo: true,
  campos: [],
});

const importForm = reactive({
  red_id: '',
  regla_id: '',
  archivo: null,
});

const toast = reactive({
  visible: false,
  message: '',
  type: 'success',
});

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 3000);
};

const reglasFiltradas = computed(() => {
  const q = reglasQuery.value.trim().toLowerCase();
  return reglas.value.filter((regla) => {
    const matchRed = !reglasRedFiltro.value || String(regla.red_id) === String(reglasRedFiltro.value);
    const matchQ =
      !q ||
      String(regla.nombre || '').toLowerCase().includes(q) ||
      String(regla.tipo_elemento || '').toLowerCase().includes(q) ||
      String(regla.archivo_patron || '').toLowerCase().includes(q);

    return matchRed && matchQ;
  });
});

const reglasImportables = computed(() => {
  if (!importForm.red_id) return [];
  return reglas.value.filter((regla) => String(regla.red_id) === String(importForm.red_id) && regla.activo);
});

const reglasTotalPages = computed(() =>
  Math.max(1, Math.ceil(reglasFiltradas.value.length / reglasPerPage.value))
);

const reglasPaginadas = computed(() => {
  const start = (reglasPage.value - 1) * reglasPerPage.value;
  return reglasFiltradas.value.slice(start, start + reglasPerPage.value);
});

const reglasRange = computed(() => {
  if (!reglasFiltradas.value.length) return { start: 0, end: 0 };
  const start = (reglasPage.value - 1) * reglasPerPage.value + 1;
  const end = Math.min(reglasPage.value * reglasPerPage.value, reglasFiltradas.value.length);
  return { start, end };
});

const reglaImportSeleccionada = computed(
  () => reglasImportables.value.find((regla) => String(regla.id) === String(importForm.regla_id)) || null
);

const archivoPatronHint = computed(() => {
  const patron = String(reglaImportSeleccionada.value?.archivo_patron || '').trim();
  return patron ? ` (${patron})` : '';
});

const importacionesTotalPages = computed(() =>
  Math.max(1, Math.ceil(importaciones.value.length / importacionesPerPage.value))
);

const importacionesPaginadas = computed(() => {
  const start = (importacionesPage.value - 1) * importacionesPerPage.value;
  return importaciones.value.slice(start, start + importacionesPerPage.value);
});

const importacionesRange = computed(() => {
  if (!importaciones.value.length) return { start: 0, end: 0 };
  const start = (importacionesPage.value - 1) * importacionesPerPage.value + 1;
  const end = Math.min(importacionesPage.value * importacionesPerPage.value, importaciones.value.length);
  return { start, end };
});

const canRevertImportacion = (item) => {
  return Boolean(item?.es_revertible);
};

const normalizeCampoDestino = (value) => {
  const key = String(value || '').trim().toLowerCase();
  if (!key) return '';
  if (['codigo', 'code', 'id'].includes(key)) return 'codigo';
  if (['nombre', 'name'].includes(key)) return 'nombre';
  if (['estado', 'status', 'm_actualexistingstate'].includes(key)) return 'estado';
  if (['external_dn', 'dn', 'ne_m_dn'].includes(key)) return 'dn_externo';
  if (['m_nativename'].includes(key)) return 'native_name';
  if (['ne_m_userlabel'].includes(key)) return 'user_label';
  if (['m_productname', 'product_name'].includes(key)) return 'nombre_producto';
  if (['t_oranetype_name', 'm_netype', 'me_type'].includes(key)) return 'tipo_equipo';
  if (['m_version'].includes(key)) return 'version_me';
  if (['m_networkaddress', 'network_address'].includes(key)) return 'direccion_red';
  if (['group_name', 'm_userlabel'].includes(key)) return 'nombre_grupo';
  return key;
};

const buildCamposPlantilla = (tipo, existing = []) => {
  const destinos = DESTINOS_POR_TIPO[tipo] || DESTINOS_POR_TIPO.nodo;
  const existingMap = new Map(
    (existing || []).map((campo) => [normalizeCampoDestino(campo.campo_destino), campo])
  );

  return destinos.map((item, idx) => {
    const destino = typeof item === 'string' ? item : item.destino;
    const fuenteSugerida = typeof item === 'string' ? '' : (item.fuente || '');
    const current = existingMap.get(destino) || {};
    return {
      columna_fuente: current.columna_fuente || fuenteSugerida,
      campo_destino: destino,
      transformacion: current.transformacion || '',
      por_defecto: current.por_defecto || '',
      es_clave_upsert: Boolean(current.es_clave_upsert),
      orden: Number(current.orden ?? (idx + 1) * 10),
      activo: current.activo !== false,
    };
  });
};

const syncCamposPorTipo = (tipo, existing = reglaForm.campos) => {
  reglaForm.campos = buildCamposPlantilla(tipo, existing);
};

watch(
  () => importForm.red_id,
  () => {
    const exists = reglasImportables.value.some((r) => String(r.id) === String(importForm.regla_id));
    if (!exists) importForm.regla_id = '';
  }
);

watch(
  () => reglaForm.tipo_elemento,
  (tipo, prev) => {
    if (tipo === prev) return;
    syncCamposPorTipo(tipo);
  }
);

const formatDate = (value) => {
  if (!value) return '-';
  try {
    return new Date(value).toLocaleString();
  } catch (_) {
    return value;
  }
};

const estadoImportacionLabel = (estado) =>
  ({
    procesando: 'Procesando',
    completado: 'Completado',
    fallido: 'Fallido',
  }[estado] || 'Desconocido');

const estadoImportacionClass = (estado) =>
  ({
    procesando: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    completado: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    fallido: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  }[estado] || 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]');

const resultadoImportacionPrincipal = (item) => {
  const estado = String(item.estado || '');
  if (estado === 'procesando') return 'Importación en proceso';
  if (estado === 'fallido') return 'Importación fallida';
  return `Procesados ${item.procesados || 0} de ${item.total_registros || 0}`;
};

const resultadoImportacionDetalle = (item) => {
  const errores = Number(item.errores_count || 0);
  const estado = String(item.estado || '');
  if (estado === 'procesando') return 'Esperando finalización';
  if (estado === 'fallido') return `${errores} error(es) detectado(s)`;

  const creados = Number(item.creados || 0);
  const actualizados = Number(item.actualizados || 0);
  const bajas = Number(item.marcados_baja || 0);
  const partes = [`Nuevos: ${creados}`, `Actualizados: ${actualizados}`];
  if (bajas > 0) partes.push(`Baja: ${bajas}`);
  if (errores > 0) partes.push(`Errores: ${errores}`);
  return partes.join(' · ');
};

const openErroresModal = async (item) => {
  showErroresModal.value = true;
  erroresLoading.value = true;
  erroresImportacionContext.value = item;
  erroresImportacion.value = [];
  erroresImportacionTotal.value = 0;

  try {
    const response = await window.axios.get(`/api/v1/admin/inventario/importaciones/${item.id}/errores`);
    const payload = response.data || {};
    erroresImportacion.value = payload.data || [];
    erroresImportacionTotal.value = Number(payload.total ?? erroresImportacion.value.length);
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudieron cargar los errores de la importación.', 'error');
  } finally {
    erroresLoading.value = false;
  }
};

const closeErroresModal = () => {
  showErroresModal.value = false;
  erroresLoading.value = false;
  erroresImportacion.value = [];
  erroresImportacionTotal.value = 0;
  erroresImportacionContext.value = null;
};

const revertirImportacion = async (item) => {
  if (!canRevertImportacion(item)) {
    showToast('Solo puedes revertir la última importación reversible por red y tipo.', 'error');
    return;
  }

  const tipo = item?.regla?.tipo_elemento || 'tipo desconocido';
  const red = item?.red?.nombre || 'red';
  const confirmado = window.confirm(
    `Se revertirán solo los elementos CREADOS por esta importación (${red} / ${tipo}). ¿Deseas continuar?`
  );
  if (!confirmado) return;

  try {
    revertingImportId.value = item.id;
    const response = await window.axios.post(`/api/v1/admin/inventario/importaciones/${item.id}/revertir`);
    const eliminados = Number(response.data?.eliminados || 0);
    showToast(`Reversión completada. Elementos eliminados: ${eliminados}.`);
    await loadData();
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo revertir la importación.', 'error');
  } finally {
    revertingImportId.value = null;
  }
};

const loadData = async () => {
  const [config, redesRes, reglasRes, importsRes] = await Promise.all([
    fetchConfiguracionSistema(),
    window.axios.get('/api/v1/admin/inventario/redes'),
    window.axios.get('/api/v1/admin/inventario/import-reglas'),
    window.axios.get('/api/v1/admin/inventario/importaciones'),
  ]);

  const map = Object.fromEntries((config || []).map((item) => [item.clave, item.valor]));
  const per = Number(map['paginacion.default_per_page'] ?? 20);
  reglasPerPage.value = Number.isFinite(per) && per > 0 ? per : 20;
  importacionesPerPage.value = Number.isFinite(per) && per > 0 ? per : 20;

  redes.value = redesRes.data?.data ?? redesRes.data;
  reglas.value = reglasRes.data?.data ?? reglasRes.data;
  importaciones.value = importsRes.data?.data ?? importsRes.data;
};

watch(importacionesTotalPages, (value) => {
  if (importacionesPage.value > value) importacionesPage.value = value;
});

watch([reglasQuery, reglasRedFiltro], () => {
  reglasPage.value = 1;
});

watch(reglasTotalPages, (value) => {
  if (reglasPage.value > value) reglasPage.value = value;
});

const resetReglaForm = () => {
  reglaForm.red_id = '';
  reglaForm.nombre = '';
  reglaForm.tipo_elemento = 'nodo';
  reglaForm.tabla_destino = 'inv_elementos_redes';
  reglaForm.archivo_patron = '';
  reglaForm.delimitador = ',';
  reglaForm.usa_comillas = true;
  reglaForm.tiene_encabezado = false;
  reglaForm.encoding = 'utf-8';
  reglaForm.activo = true;
  reglaForm.campos = buildCamposPlantilla('nodo');
  Object.keys(reglaErrors).forEach((k) => (reglaErrors[k] = ''));
};

const openReglaCreate = () => {
  resetReglaForm();
  editingReglaId.value = null;
  showReglaModal.value = true;
};

const openReglaEdit = (regla) => {
  resetReglaForm();
  editingReglaId.value = regla.id;
  reglaForm.red_id = String(regla.red_id || '');
  reglaForm.nombre = regla.nombre || '';
  reglaForm.tipo_elemento = regla.tipo_elemento || 'nodo';
  reglaForm.tabla_destino = regla.tabla_destino || 'inv_elementos_redes';
  reglaForm.archivo_patron = regla.archivo_patron || '';
  reglaForm.delimitador = regla.delimitador || ',';
  reglaForm.usa_comillas = Boolean(regla.usa_comillas);
  reglaForm.tiene_encabezado = Boolean(regla.tiene_encabezado);
  reglaForm.encoding = regla.encoding || 'utf-8';
  reglaForm.activo = Boolean(regla.activo);
  const campos = (regla.campos || []).map((campo, idx) => ({
    columna_fuente: campo.columna_fuente || '',
    campo_destino: normalizeCampoDestino(campo.campo_destino),
    transformacion: campo.transformacion || '',
    por_defecto: campo.por_defecto || '',
    es_clave_upsert: Boolean(campo.es_clave_upsert),
    orden: Number(campo.orden ?? (idx + 1) * 10),
    activo: campo.activo !== false,
  }));
  syncCamposPorTipo(reglaForm.tipo_elemento, campos);
  showReglaModal.value = true;
};

const closeReglaModal = () => {
  showReglaModal.value = false;
};

const saveRegla = async () => {
  Object.keys(reglaErrors).forEach((k) => (reglaErrors[k] = ''));

  const payload = {
    red_id: reglaForm.red_id ? Number(reglaForm.red_id) : null,
    nombre: reglaForm.nombre,
    tipo_elemento: reglaForm.tipo_elemento,
    tabla_destino: 'inv_elementos_redes',
    archivo_patron: reglaForm.archivo_patron || null,
    delimitador: reglaForm.delimitador || ',',
    usa_comillas: Boolean(reglaForm.usa_comillas),
    tiene_encabezado: Boolean(reglaForm.tiene_encabezado),
    encoding: reglaForm.encoding || 'utf-8',
    activo: Boolean(reglaForm.activo),
    campos: reglaForm.campos.map((campo) => ({
      columna_fuente: campo.columna_fuente,
      campo_destino: campo.campo_destino,
      transformacion: campo.transformacion || null,
      por_defecto: campo.por_defecto || null,
      es_clave_upsert: Boolean(campo.es_clave_upsert),
      orden: Number(campo.orden || 0),
      activo: campo.activo !== false,
    })),
  };

  try {
    if (editingReglaId.value) {
      await window.axios.put(`/api/v1/admin/inventario/import-reglas/${editingReglaId.value}`, payload);
      showToast('Regla actualizada.');
    } else {
      await window.axios.post('/api/v1/admin/inventario/import-reglas', payload);
      showToast('Regla creada.');
    }

    await loadData();
    closeReglaModal();
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      reglaErrors[field] = messages?.[0] || 'Dato inválido';
    });

    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar la regla.', 'error');
    }
  }
};

const deleteRegla = async (regla) => {
  if (regla.in_use) {
    showToast('No se puede eliminar: la regla ya tiene importaciones.', 'error');
    return;
  }

  if (!window.confirm(`¿Eliminar la regla "${regla.nombre}"?`)) return;

  try {
    await window.axios.delete(`/api/v1/admin/inventario/import-reglas/${regla.id}`);
    await loadData();
    showToast('Regla eliminada.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar la regla.', 'error');
  }
};

const onFileChange = (event) => {
  importForm.archivo = event.target.files?.[0] || null;
};

const runImport = async () => {
  if (!importForm.red_id || !importForm.regla_id || !importForm.archivo) {
    showToast('Selecciona red, regla y archivo para ejecutar la importación.', 'error');
    return;
  }

  const reglaSeleccionada = reglas.value.find((regla) => String(regla.id) === String(importForm.regla_id));
  const redSeleccionada = redes.value.find((red) => String(red.id) === String(importForm.red_id));
  const nombreRed = redSeleccionada?.nombre || 'No disponible';
  const nombreRegla = reglaSeleccionada?.nombre || 'No disponible';
  const tipoRegla = reglaSeleccionada?.tipo_elemento || 'No disponible';
  const confirmado = window.confirm(
    [
      'Antes de ejecutar la importación:',
      '- La reversión solo elimina elementos nuevos creados por esa ejecución.',
      '- Si esta carga solo actualiza (sin nuevos), quedará como "Sin nuevos" y no tendrá opción de reversión.',
      '- Una nueva carga puede dejar sin opción de reversión la ejecución anterior de la misma red y tipo.',
      '',
      `Red: ${nombreRed}`,
      `Regla: ${nombreRegla}`,
      `Tipo: ${tipoRegla}`,
      '',
      '¿Deseas continuar?',
    ].join('\n')
  );
  if (!confirmado) return;

  const formData = new FormData();
  formData.append('red_id', String(importForm.red_id));
  formData.append('regla_id', String(importForm.regla_id));
  formData.append('archivo', importForm.archivo);

  try {
    runningImport.value = true;
    const response = await window.axios.post('/api/v1/admin/inventario/importaciones/ejecutar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    showToast(response.data?.message || 'Importación ejecutada.');
    importForm.archivo = null;
    await loadData();
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo ejecutar la importación.', 'error');
  } finally {
    runningImport.value = false;
  }
};

onMounted(loadData);
</script>
