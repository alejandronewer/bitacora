<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Configuración</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Configuración</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Parámetros base del portal y preferencias de usuario.</p>
        </div>
      </div>

      <div class="inline-flex gap-2 rounded-full bg-slate-100 dark:bg-border-dark p-1">
        <button
          v-if="isAdmin"
          class="px-4 py-2 rounded-full text-sm font-semibold transition-colors"
          :class="activeTab === 'sistema' ? 'bg-primary text-white shadow-md shadow-primary/30' : 'text-slate-600 dark:text-[#92adc9] hover:text-primary'"
          type="button"
          @click="activeTab = 'sistema'"
        >
          Configuración del Sistema
        </button>
        <button
          v-if="canSeeUserConfig"
          class="px-4 py-2 rounded-full text-sm font-semibold transition-colors"
          :class="activeTab === 'usuario' ? 'bg-primary text-white shadow-md shadow-primary/30' : 'text-slate-600 dark:text-[#92adc9] hover:text-primary'"
          type="button"
          @click="activeTab = 'usuario'"
        >
          Configuración de Usuario
        </button>
      </div>

      <section v-if="activeTab === 'sistema' && isAdmin" class="space-y-6">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">General</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ generalConfiguracion.length }} parámetros</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm divide-y divide-slate-100 dark:divide-border-dark/60">
            <div
              v-for="item in generalConfiguracion"
              :key="item.id"
              class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3"
              :class="isDirty(item) ? 'bg-primary/5' : ''"
            >
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.descripcion || item.clave }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9] mt-1">Clave: {{ item.clave }}</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <input
                  v-model="item.valor"
                  :type="item.tipo === 'int' ? 'number' : 'text'"
                  class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                  @input="handleChange(item)"
                />
                <button
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="buttonClass(item)"
                  :disabled="!isDirty(item) || isSaving(item)"
                  type="button"
                  @click="saveItem(item)"
                >
                  <span class="material-symbols-outlined text-base">
                    {{ isSaving(item) ? 'progress_activity' : isSaved(item) ? 'check_circle' : 'save' }}
                  </span>
                  {{ isSaving(item) ? 'Guardando...' : isSaved(item) ? 'Guardado' : 'Guardar' }}
                </button>
              </div>
              <p v-if="errorById[item.id]" class="pt-2 text-xs text-red-500">{{ errorById[item.id] }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Tema del portal</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ temaConfiguracion.length }} parámetros</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm divide-y divide-slate-100 dark:divide-border-dark/60">
            <div
              v-for="item in temaConfiguracion"
              :key="item.id"
              class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3"
              :class="isDirty(item) ? 'bg-primary/5' : ''"
            >
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.descripcion || item.clave }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9] mt-1">Clave: {{ item.clave }}</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <template v-if="item.tipo === 'bool'">
                  <button
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg font-semibold text-xs transition-colors"
                    :class="item.valor === '1' || item.valor === 1 || item.valor === true ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    type="button"
                    @click="toggleBool(item)"
                  >
                    <span class="material-symbols-outlined text-base">{{ isTruthy(item.valor) ? 'toggle_on' : 'toggle_off' }}</span>
                    {{ isTruthy(item.valor) ? 'Activo' : 'Inactivo' }}
                  </button>
                </template>
                <template v-else-if="item.clave === 'tema.modo'">
                  <select
                    v-model="item.valor"
                    class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                    @change="handleChange(item)"
                  >
                    <option value="dark">Oscuro</option>
                    <option value="light">Claro</option>
                  </select>
                </template>
                <template v-else>
                  <input
                    v-model="item.valor"
                    :type="item.tipo === 'int' ? 'number' : 'text'"
                    class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                    @input="handleChange(item)"
                  />
                </template>
                <button
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="buttonClass(item)"
                  :disabled="!isDirty(item) || isSaving(item)"
                  type="button"
                  @click="saveItem(item)"
                >
                  <span class="material-symbols-outlined text-base">
                    {{ isSaving(item) ? 'progress_activity' : isSaved(item) ? 'check_circle' : 'save' }}
                  </span>
                  {{ isSaving(item) ? 'Guardando...' : isSaved(item) ? 'Guardado' : 'Guardar' }}
                </button>
              </div>
              <p v-if="errorById[item.id]" class="pt-2 text-xs text-red-500">{{ errorById[item.id] }}</p>
            </div>
          </div>
          <div v-if="temaConfiguracion.length === 0" class="px-5 py-4 text-sm text-slate-500 dark:text-[#92adc9]">
            Sin parámetros definidos para tema.
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Entradas de Bitácora</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ bitacoraConfiguracion.length }} parámetros</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm divide-y divide-slate-100 dark:divide-border-dark/60">
            <div
              v-for="item in bitacoraConfiguracion"
              :key="item.id"
              class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3"
              :class="isDirty(item) ? 'bg-primary/5' : ''"
            >
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.descripcion || item.clave }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9] mt-1">Clave: {{ item.clave }}</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <template v-if="item.tipo === 'bool'">
                  <button
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg font-semibold text-xs transition-colors"
                    :class="item.valor === '1' || item.valor === 1 || item.valor === true ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    type="button"
                    @click="toggleBool(item)"
                  >
                    <span class="material-symbols-outlined text-base">{{ isTruthy(item.valor) ? 'toggle_on' : 'toggle_off' }}</span>
                    {{ isTruthy(item.valor) ? 'Activo' : 'Inactivo' }}
                  </button>
                </template>
                <template v-else>
                  <input
                    v-model="item.valor"
                    :type="item.tipo === 'int' ? 'number' : 'text'"
                    class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                    @input="handleChange(item)"
                  />
                </template>
                <button
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="buttonClass(item)"
                  :disabled="!isDirty(item) || isSaving(item)"
                  type="button"
                  @click="saveItem(item)"
                >
                  <span class="material-symbols-outlined text-base">
                    {{ isSaving(item) ? 'progress_activity' : isSaved(item) ? 'check_circle' : 'save' }}
                  </span>
                  {{ isSaving(item) ? 'Guardando...' : isSaved(item) ? 'Guardado' : 'Guardar' }}
                </button>
              </div>
              <p v-if="errorById[item.id]" class="pt-2 text-xs text-red-500">{{ errorById[item.id] }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Enlaces</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ enlacesConfiguracion.length }} parámetros</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm divide-y divide-slate-100 dark:divide-border-dark/60">
            <div
              v-for="item in enlacesConfiguracion"
              :key="item.id"
              class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3"
              :class="isDirty(item) ? 'bg-primary/5' : ''"
            >
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.descripcion || item.clave }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9] mt-1">Clave: {{ item.clave }}</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <input
                  v-model="item.valor"
                  type="text"
                  class="w-full sm:w-72 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                  @input="handleChange(item)"
                />
                <button
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="buttonClass(item)"
                  :disabled="!isDirty(item) || isSaving(item)"
                  type="button"
                  @click="saveItem(item)"
                >
                  <span class="material-symbols-outlined text-base">
                    {{ isSaving(item) ? 'progress_activity' : isSaved(item) ? 'check_circle' : 'save' }}
                  </span>
                  {{ isSaving(item) ? 'Guardando...' : isSaved(item) ? 'Guardado' : 'Guardar' }}
                </button>
              </div>
              <p v-if="errorById[item.id]" class="pt-2 text-xs text-red-500">{{ errorById[item.id] }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Imágenes adjuntas</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ imagenesConfiguracion.length }} parámetros</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm divide-y divide-slate-100 dark:divide-border-dark/60">
            <div
              v-for="item in imagenesConfiguracion"
              :key="item.id"
              class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3"
              :class="isDirty(item) ? 'bg-primary/5' : ''"
            >
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.descripcion || item.clave }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9] mt-1">Clave: {{ item.clave }}</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <template v-if="item.tipo === 'bool'">
                  <button
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg font-semibold text-xs transition-colors"
                    :class="item.valor === '1' || item.valor === 1 || item.valor === true ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    type="button"
                    @click="toggleBool(item)"
                  >
                    <span class="material-symbols-outlined text-base">{{ isTruthy(item.valor) ? 'toggle_on' : 'toggle_off' }}</span>
                    {{ isTruthy(item.valor) ? 'Activo' : 'Inactivo' }}
                  </button>
                </template>
                <template v-else-if="item.clave === 'tema.modo'">
                  <select
                    v-model="item.valor"
                    class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                    @change="handleChange(item)"
                  >
                    <option value="dark">Oscuro</option>
                    <option value="light">Claro</option>
                  </select>
                </template>
                <template v-else>
                  <input
                    v-model="item.valor"
                    :type="item.tipo === 'int' ? 'number' : 'text'"
                    class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                    @input="handleChange(item)"
                  />
                </template>
                <button
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="buttonClass(item)"
                  :disabled="!isDirty(item) || isSaving(item)"
                  type="button"
                  @click="saveItem(item)"
                >
                  <span class="material-symbols-outlined text-base">
                    {{ isSaving(item) ? 'progress_activity' : isSaved(item) ? 'check_circle' : 'save' }}
                  </span>
                  {{ isSaving(item) ? 'Guardando...' : isSaved(item) ? 'Guardado' : 'Guardar' }}
                </button>
              </div>
              <p v-if="errorById[item.id]" class="pt-2 text-xs text-red-500">{{ errorById[item.id] }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Archivos adjuntos</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">{{ archivosConfiguracion.length }} parámetros</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm divide-y divide-slate-100 dark:divide-border-dark/60">
            <div
              v-for="item in archivosConfiguracion"
              :key="item.id"
              class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3"
              :class="isDirty(item) ? 'bg-primary/5' : ''"
            >
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.descripcion || item.clave }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9] mt-1">Clave: {{ item.clave }}</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <template v-if="item.tipo === 'bool'">
                  <button
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg font-semibold text-xs transition-colors"
                    :class="isTruthy(item.valor) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-600 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    type="button"
                    @click="toggleBool(item)"
                  >
                    <span class="material-symbols-outlined text-base">{{ isTruthy(item.valor) ? 'toggle_on' : 'toggle_off' }}</span>
                    {{ isTruthy(item.valor) ? 'Activo' : 'Inactivo' }}
                  </button>
                </template>
                <template v-else>
                  <input
                    v-model="item.valor"
                    :type="item.tipo === 'int' ? 'number' : 'text'"
                    class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary outline-none"
                    @input="handleChange(item)"
                  />
                </template>
                <button
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="buttonClass(item)"
                  :disabled="!isDirty(item) || isSaving(item)"
                  type="button"
                  @click="saveItem(item)"
                >
                  <span class="material-symbols-outlined text-base">
                    {{ isSaving(item) ? 'progress_activity' : isSaved(item) ? 'check_circle' : 'save' }}
                  </span>
                  {{ isSaving(item) ? 'Guardando...' : isSaved(item) ? 'Guardado' : 'Guardar' }}
                </button>
              </div>
              <p v-if="errorById[item.id]" class="pt-2 text-xs text-red-500">{{ errorById[item.id] }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Mantenimiento</h4>
            <span class="text-xs text-slate-500 dark:text-[#92adc9]">Solo administrador</span>
          </div>
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-5 space-y-4">
            <p class="text-sm text-slate-600 dark:text-[#92adc9]">
              Limpia adjuntos temporales sin entrada asociada y archivos huérfanos en disco.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-[220px_auto] gap-3 items-end">
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Antigüedad mínima (horas)</label>
                <input
                  v-model.number="cleanupForm.hours"
                  type="number"
                  min="1"
                  max="720"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                  :class="cleanupStatusType === 'error' ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
                />
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="cleanupLoading ? 'bg-slate-200 text-slate-500 cursor-wait shadow-none' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#d4e4f6]'"
                  :disabled="cleanupLoading"
                  @click="runAdjuntosCleanup(true)"
                >
                  <span class="material-symbols-outlined text-base">{{ cleanupLoading ? 'progress_activity' : 'fact_check' }}</span>
                  Simular limpieza
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="cleanupLoading ? 'bg-primary/80 text-white cursor-wait' : 'bg-primary hover:bg-primary/90 text-white shadow-primary/25'"
                  :disabled="cleanupLoading"
                  @click="runAdjuntosCleanup(false)"
                >
                  <span class="material-symbols-outlined text-base">{{ cleanupLoading ? 'progress_activity' : 'cleaning_services' }}</span>
                  Ejecutar limpieza
                </button>
              </div>
            </div>
            <p
              v-if="cleanupStatusMessage"
              class="text-xs"
              :class="cleanupStatusType === 'error' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
            >
              {{ cleanupStatusMessage }}
            </p>
            <p v-else class="text-xs text-slate-500 dark:text-[#92adc9]">
              Recomendado: primero simular, luego ejecutar.
            </p>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-5 space-y-4">
            <p class="text-sm text-slate-600 dark:text-[#92adc9]">
              Limpia archivos usados para importar Inventario NMS/EMS y cargas SAP-like en <code>storage/app/imports</code>.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-[220px_auto] gap-3 items-end">
              <div>
                <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Antigüedad mínima (horas)</label>
                <input
                  v-model.number="importCleanupForm.hours"
                  type="number"
                  min="1"
                  max="720"
                  class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                  :class="importCleanupStatusType === 'error' ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
                />
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="importCleanupLoading ? 'bg-slate-200 text-slate-500 cursor-wait shadow-none' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#d4e4f6]'"
                  :disabled="importCleanupLoading"
                  @click="runImportFilesCleanup(true)"
                >
                  <span class="material-symbols-outlined text-base">{{ importCleanupLoading ? 'progress_activity' : 'fact_check' }}</span>
                  Simular limpieza
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                  :class="importCleanupLoading ? 'bg-primary/80 text-white cursor-wait' : 'bg-primary hover:bg-primary/90 text-white shadow-primary/25'"
                  :disabled="importCleanupLoading"
                  @click="runImportFilesCleanup(false)"
                >
                  <span class="material-symbols-outlined text-base">{{ importCleanupLoading ? 'progress_activity' : 'cleaning_services' }}</span>
                  Ejecutar limpieza
                </button>
              </div>
            </div>
            <p
              v-if="importCleanupStatusMessage"
              class="text-xs"
              :class="importCleanupStatusType === 'error' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
            >
              {{ importCleanupStatusMessage }}
            </p>
            <p v-else class="text-xs text-slate-500 dark:text-[#92adc9]">
              Recomendado: primero simular, luego ejecutar.
            </p>
          </div>
        </div>
      </section>

      <section v-else-if="activeTab === 'usuario' && canSeeUserConfig" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div>
              <h4 class="text-lg font-bold text-slate-900 dark:text-white">Tema personal</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Sobrescribe el tema definido por el sistema.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
              <select
                v-if="userThemeItem"
                v-model="userThemeItem.valor"
                class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                @change="handleUserChange(userThemeItem)"
              >
                <option value="global">Global (usar sistema)</option>
                <option value="dark">Oscuro</option>
                <option value="light">Claro</option>
              </select>
              <button
                v-if="userThemeItem"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                :class="userButtonClass(userThemeItem)"
                :disabled="!isUserDirty(userThemeItem) || isUserSaving(userThemeItem)"
                type="button"
                @click="saveUserItem(userThemeItem)"
              >
                <span class="material-symbols-outlined text-base">
                  {{ isUserSaving(userThemeItem) ? 'progress_activity' : isUserSaved(userThemeItem) ? 'check_circle' : 'save' }}
                </span>
                {{ isUserSaving(userThemeItem) ? 'Guardando...' : isUserSaved(userThemeItem) ? 'Guardado' : 'Guardar' }}
              </button>
            </div>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">
              Tema del sistema: <span class="font-semibold">{{ systemThemeLabel }}</span>
            </p>
            <p v-if="userErrorByKey['tema.modo']" class="text-xs text-red-500">{{ userErrorByKey['tema.modo'] }}</p>
          </div>

          <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
            <div>
              <h4 class="text-lg font-bold text-slate-900 dark:text-white">Densidad de la interfaz</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Ajusta el tamaño general de la UI.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
              <select
                v-if="userDensityItem"
                v-model="userDensityItem.valor"
                class="w-full sm:w-56 bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                @change="handleUserChange(userDensityItem)"
              >
                <option value="compacta">Compacta</option>
                <option value="normal">Normal</option>
                <option value="grande">Grande</option>
              </select>
              <button
                v-if="userDensityItem"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                :class="userButtonClass(userDensityItem)"
                :disabled="!isUserDirty(userDensityItem) || isUserSaving(userDensityItem)"
                type="button"
                @click="saveUserItem(userDensityItem)"
              >
                <span class="material-symbols-outlined text-base">
                  {{ isUserSaving(userDensityItem) ? 'progress_activity' : isUserSaved(userDensityItem) ? 'check_circle' : 'save' }}
                </span>
                {{ isUserSaving(userDensityItem) ? 'Guardando...' : isUserSaved(userDensityItem) ? 'Guardado' : 'Guardar' }}
              </button>
            </div>
            <p v-if="userErrorByKey['ui.densidad']" class="text-xs text-red-500">{{ userErrorByKey['ui.densidad'] }}</p>
          </div>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-lg font-bold text-slate-900 dark:text-white">Ícono de perfil</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Selecciona el ícono que se mostrará en el menú lateral.</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                v-if="userSidebarIconItem"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
                :class="userButtonClass(userSidebarIconItem)"
                :disabled="!isUserDirty(userSidebarIconItem) || isUserSaving(userSidebarIconItem)"
                type="button"
                @click="saveUserItem(userSidebarIconItem)"
              >
                <span class="material-symbols-outlined text-base">
                  {{ isUserSaving(userSidebarIconItem) ? 'progress_activity' : isUserSaved(userSidebarIconItem) ? 'check_circle' : 'save' }}
                </span>
                {{ isUserSaving(userSidebarIconItem) ? 'Guardando...' : isUserSaved(userSidebarIconItem) ? 'Guardado' : 'Guardar' }}
              </button>
            </div>
          </div>
          <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-10 gap-2">
            <button
              v-for="icon in avatarIcons"
              :key="icon"
              type="button"
              class="flex items-center justify-center rounded-lg border border-transparent p-2 transition-all"
              :class="icon === userSidebarIconItem?.valor ? 'bg-primary/10 border-primary text-primary' : 'bg-slate-50 dark:bg-border-dark/60 text-slate-500 dark:text-[#92adc9]'"
              @click="selectUserIcon(icon)"
            >
              <span class="material-symbols-outlined text-xl">{{ icon }}</span>
            </button>
          </div>
          <p class="text-xs text-slate-500 dark:text-[#92adc9]">
            Icono actual: <span class="font-semibold text-slate-700 dark:text-white">{{ userSidebarIconItem?.valor || 'account_circle' }}</span>
          </p>
          <p v-if="userErrorByKey['ui.sidebar_icon']" class="text-xs text-red-500">{{ userErrorByKey['ui.sidebar_icon'] }}</p>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-lg font-bold text-slate-900 dark:text-white">Orden de widgets</h4>
              <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Reordena los bloques principales del dashboard.</p>
            </div>
            <button
              v-if="userWidgetOrderItem"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
              :class="userButtonClass(userWidgetOrderItem)"
              :disabled="!isUserDirty(userWidgetOrderItem) || isUserSaving(userWidgetOrderItem)"
              type="button"
              @click="saveUserItem(userWidgetOrderItem)"
            >
              <span class="material-symbols-outlined text-base">
                {{ isUserSaving(userWidgetOrderItem) ? 'progress_activity' : isUserSaved(userWidgetOrderItem) ? 'check_circle' : 'save' }}
              </span>
              {{ isUserSaving(userWidgetOrderItem) ? 'Guardando...' : isUserSaved(userWidgetOrderItem) ? 'Guardado' : 'Guardar' }}
            </button>
          </div>
          <ul class="space-y-2">
            <li
              v-for="(item, index) in widgetOrderResolved"
              :key="item.id"
              class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-slate-200/70 dark:border-border-dark px-4 py-3"
            >
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.label }}</p>
                <p class="text-[11px] text-slate-500 dark:text-[#92adc9]">ID: {{ item.id }}</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-border-dark px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-[#92adc9] hover:bg-slate-100 dark:hover:bg-border-dark/60 transition-colors"
                  :disabled="index === 0"
                  @click="moveWidget(item.id, -1)"
                >
                  <span class="material-symbols-outlined text-base">arrow_upward</span>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-border-dark px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-[#92adc9] hover:bg-slate-100 dark:hover:bg-border-dark/60 transition-colors"
                  :disabled="index === widgetOrderResolved.length - 1"
                  @click="moveWidget(item.id, 1)"
                >
                  <span class="material-symbols-outlined text-base">arrow_downward</span>
                </button>
              </div>
            </li>
          </ul>
          <p v-if="userErrorByKey['dashboard.widgets_order']" class="text-xs text-red-500">
            {{ userErrorByKey['dashboard.widgets_order'] }}
          </p>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm p-6 space-y-4">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Cambiar contraseña</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">Ingresa tu contraseña actual y confirma la nueva contraseña.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Contraseña actual</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                autocomplete="current-password"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="passwordErrors.current_password ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="passwordErrors.current_password" class="mt-1 text-xs text-red-500">{{ passwordErrors.current_password }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nueva contraseña</label>
              <input
                v-model="passwordForm.password"
                type="password"
                autocomplete="new-password"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="passwordErrors.password ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="passwordErrors.password" class="mt-1 text-xs text-red-500">{{ passwordErrors.password }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Confirmar nueva contraseña</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                autocomplete="new-password"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="passwordErrors.password_confirmation ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="passwordErrors.password_confirmation" class="mt-1 text-xs text-red-500">{{ passwordErrors.password_confirmation }}</p>
            </div>
          </div>
          <div class="flex items-center justify-between gap-3">
            <p
              v-if="passwordStatusMessage"
              class="text-xs"
              :class="passwordStatusType === 'error' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
            >
              {{ passwordStatusMessage }}
            </p>
            <span v-else class="text-xs text-slate-500 dark:text-[#92adc9]">La contraseña debe cumplir las reglas mínimas de seguridad.</span>
            <button
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold text-xs transition-all shadow-lg"
              :class="passwordSaving ? 'bg-primary/80 text-white cursor-wait' : 'bg-primary hover:bg-primary/90 text-white shadow-primary/25'"
              :disabled="passwordSaving"
              type="button"
              @click="updatePassword"
            >
              <span class="material-symbols-outlined text-base">{{ passwordSaving ? 'progress_activity' : 'lock_reset' }}</span>
              {{ passwordSaving ? 'Actualizando...' : 'Actualizar contraseña' }}
            </button>
          </div>
        </div>
      </section>
      <div v-else class="text-sm text-slate-500 dark:text-[#92adc9]">
        No tienes permisos para ver esta sección.
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../layouts/DashboardLayout.vue';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { getAuthUser, peekAuthUser } from '../auth';
import { applyDensityValue, applyThemeValue } from '../theme';

const activeTab = ref('usuario');
const configuracionSistema = ref([]);
const configuracionUsuario = ref([]);
const originalValues = ref(new Map());
const savingIds = ref(new Set());
const savedIds = ref(new Set());
const errorById = ref({});
const userOriginalValues = ref(new Map());
const userSavingKeys = ref(new Set());
const userSavedKeys = ref(new Set());
const userErrorByKey = ref({});
const userRoles = ref([]);
const roleReady = ref(false);
const passwordSaving = ref(false);
const passwordStatusMessage = ref('');
const passwordStatusType = ref('');
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const cleanupLoading = ref(false);
const cleanupStatusMessage = ref('');
const cleanupStatusType = ref('');
const cleanupForm = reactive({
  hours: 24,
});
const importCleanupLoading = ref(false);
const importCleanupStatusMessage = ref('');
const importCleanupStatusType = ref('');
const importCleanupForm = reactive({
  hours: 24,
});
const passwordErrors = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const avatarIcons = [
  'account_circle',
  'person',
  'person_outline',
  'badge',
  'assignment_ind',
  'engineering',
  'support_agent',
  'admin_panel_settings',
  'verified_user',
  'supervisor_account',
  'manage_accounts',
  'contact_page',
  'work',
  'business_center',
  'face',
  'emoji_people',
  'person_pin',
  'fingerprint',
  'vpn_key',
  'security',
];

const widgetOptions = [
  { id: 'resumen', label: 'Resumen general' },
  { id: 'mensuales', label: 'Widgets mensuales' },
  { id: 'operativo_inventario', label: 'Operativo vs Inventario' },
  { id: 'pm', label: 'PM-like por cuatrimestre' },
  { id: 'criterio', label: 'Criterio temático' },
  { id: 'usuarios', label: 'Usuarios activos' },
];

const defaultWidgetOrder = widgetOptions.map((item) => item.id);

const imagenesConfiguracion = computed(() =>
  configuracionSistema.value.filter((item) => item.clave?.startsWith('imagenes.'))
);

const temaConfiguracion = computed(() =>
  configuracionSistema.value.filter((item) => item.clave?.startsWith('tema.'))
);

const enlacesConfiguracion = computed(() =>
  configuracionSistema.value.filter((item) => item.clave?.startsWith('enlaces.'))
);

const archivosConfiguracion = computed(() =>
  configuracionSistema.value.filter((item) => item.clave?.startsWith('archivos.'))
);

const bitacoraConfiguracion = computed(() =>
  configuracionSistema.value.filter((item) =>
    ['bitacora_publica', 'max_adjuntos_por_entrada'].includes(item.clave)
  )
);

const temaModo = computed(() =>
  configuracionSistema.value.find((item) => item.clave === 'tema.modo')?.valor
);

const generalConfiguracion = computed(() =>
  configuracionSistema.value.filter((item) =>
    ['app_nombre', 'app_siglas', 'paginacion.default_per_page', 'timezone'].includes(item.clave)
  )
);

const otrasConfiguraciones = computed(() =>
  configuracionSistema.value.filter(
    (item) =>
      !item.clave?.startsWith('imagenes.') &&
      !item.clave?.startsWith('tema.') &&
      !item.clave?.startsWith('enlaces.') &&
      !item.clave?.startsWith('archivos.') &&
      !['bitacora_publica', 'max_adjuntos_por_entrada'].includes(item.clave) &&
      !['app_nombre', 'app_siglas', 'paginacion.default_per_page', 'timezone'].includes(item.clave)
  )
);

const userConfigByKey = computed(() =>
  Object.fromEntries(configuracionUsuario.value.map((item) => [item.clave, item]))
);

const userThemeItem = computed(() => userConfigByKey.value['tema.modo']);
const userDensityItem = computed(() => userConfigByKey.value['ui.densidad']);
const userSidebarIconItem = computed(() => userConfigByKey.value['ui.sidebar_icon']);
const userWidgetOrderItem = computed(() => userConfigByKey.value['dashboard.widgets_order']);

const roleNames = computed(() =>
  (userRoles.value || [])
    .map((role) => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean)
    .map((role) => role.toLowerCase())
);

const isAdmin = computed(() => roleNames.value.includes('administrador'));
const isOperador = computed(() => roleNames.value.includes('operador'));
const canSeeUserConfig = computed(() => isAdmin.value || isOperador.value);

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

const widgetOrderResolved = computed(() => {
  const order = normalizeWidgetOrder(userWidgetOrderItem.value?.valor);
  return order.map((id) => widgetOptions.find((item) => item.id === id)).filter(Boolean);
});

const isTruthy = (value) => value === true || value === 1 || value === '1' || value === 'true';

const toggleBool = (item) => {
  item.valor = isTruthy(item.valor) ? '0' : '1';
  handleChange(item);
};

const isDirty = (item) => {
  const original = originalValues.value.get(item.id);
  return String(item.valor ?? '') !== String(original ?? '');
};

const isSaving = (item) => savingIds.value.has(item.id);

const isSaved = (item) => savedIds.value.has(item.id);

const buttonClass = (item) => {
  if (isSaving(item)) {
    return 'bg-primary/80 text-white cursor-wait';
  }
  if (isSaved(item) && !isDirty(item)) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
  }
  if (!isDirty(item)) {
    return 'bg-slate-100 text-slate-500 dark:bg-border-dark/60 dark:text-[#92adc9] shadow-none';
  }
  return 'bg-primary hover:bg-primary/90 text-white shadow-primary/25';
};

const isThemeGlobal = (value) => !value || String(value).toLowerCase() === 'global';

const resolveSystemTheme = () => (temaModo.value === 'light' ? 'light' : 'dark');

const systemThemeLabel = computed(() => (resolveSystemTheme() === 'light' ? 'Claro' : 'Oscuro'));

const handleChange = (item) => {
  if (isSaved(item)) {
    const next = new Set(savedIds.value);
    next.delete(item.id);
    savedIds.value = next;
  }
};

const isUserDirty = (item) => {
  const original = userOriginalValues.value.get(item.clave);
  return String(item.valor ?? '') !== String(original ?? '');
};

const isUserSaving = (item) => userSavingKeys.value.has(item.clave);

const isUserSaved = (item) => userSavedKeys.value.has(item.clave);

const userButtonClass = (item) => {
  if (isUserSaving(item)) {
    return 'bg-primary/80 text-white cursor-wait';
  }
  if (isUserSaved(item) && !isUserDirty(item)) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
  }
  if (!isUserDirty(item)) {
    return 'bg-slate-100 text-slate-500 dark:bg-border-dark/60 dark:text-[#92adc9] shadow-none';
  }
  return 'bg-primary hover:bg-primary/90 text-white shadow-primary/25';
};

const handleUserChange = (item) => {
  if (!item) {
    return;
  }
  if (item.clave === 'tema.modo') {
    // Se aplica al guardar, no al cambiar el selector.
  }
  if (item.clave === 'ui.densidad') {
    applyDensityValue(item.valor);
  }
  if (item.clave === 'ui.sidebar_icon') {
    window.dispatchEvent(new CustomEvent('bitacora:user-pref', { detail: { clave: item.clave, valor: item.valor } }));
  }

  if (isUserSaved(item)) {
    const next = new Set(userSavedKeys.value);
    next.delete(item.clave);
    userSavedKeys.value = next;
  }
};

const selectUserIcon = (icon) => {
  if (!userSidebarIconItem.value) {
    return;
  }
  userSidebarIconItem.value.valor = icon;
  handleUserChange(userSidebarIconItem.value);
};

const moveWidget = (id, direction) => {
  if (!userWidgetOrderItem.value) {
    return;
  }
  const order = normalizeWidgetOrder(userWidgetOrderItem.value.valor);
  const index = order.indexOf(id);
  const target = index + direction;
  if (index === -1 || target < 0 || target >= order.length) {
    return;
  }
  const next = [...order];
  const [moved] = next.splice(index, 1);
  next.splice(target, 0, moved);
  userWidgetOrderItem.value.valor = next.join(',');
  handleUserChange(userWidgetOrderItem.value);
};

const applyRoles = (data) => {
  if (!data) {
    userRoles.value = [];
    return;
  }
  const roles = data.roles || [];
  userRoles.value = Array.isArray(roles) ? roles : [];
};

const syncActiveTab = () => {
  if (!roleReady.value) {
    activeTab.value = isAdmin.value ? 'sistema' : 'usuario';
    roleReady.value = true;
    return;
  }
  if (!isAdmin.value && activeTab.value === 'sistema') {
    activeTab.value = 'usuario';
  }
};

const loadUserRole = async () => {
  const cached = peekAuthUser();
  if (cached) {
    applyRoles(cached);
    syncActiveTab();
  }
  try {
    const data = await getAuthUser();
    applyRoles(data);
  } finally {
    syncActiveTab();
  }
};

const loadData = async () => {
  const response = await window.axios.get('/api/v1/configuracion/sistema');
  configuracionSistema.value = response.data?.data ?? response.data;

  const nextOriginal = new Map();
  configuracionSistema.value.forEach((item) => nextOriginal.set(item.id, item.valor));
  originalValues.value = nextOriginal;

  if (temaModo.value) {
    applyThemeValue(temaModo.value);
  }

  await loadUserConfig();
};

const loadUserConfig = async () => {
  try {
    const response = await window.axios.get('/api/v1/configuracion/usuario');
    const items = response.data?.data ?? response.data;
    const map = new Map((items || []).map((item) => [item.clave, item]));

    const defaults = [
      {
        clave: 'tema.modo',
        valor: 'global',
        tipo: 'string',
        descripcion: 'Tema personal',
      },
      {
        clave: 'ui.densidad',
        valor: 'normal',
        tipo: 'string',
        descripcion: 'Densidad de interfaz',
      },
      {
        clave: 'ui.sidebar_icon',
        valor: 'account_circle',
        tipo: 'string',
        descripcion: 'Icono de perfil',
      },
      {
        clave: 'dashboard.widgets_order',
        valor: defaultWidgetOrder.join(','),
        tipo: 'string',
        descripcion: 'Orden de widgets',
      },
    ];

    configuracionUsuario.value = defaults.map((def) => {
      const existing = map.get(def.clave);
      return {
        ...def,
        ...existing,
        valor: existing?.valor ?? def.valor,
      };
    });

    const nextOriginal = new Map();
    configuracionUsuario.value.forEach((item) => nextOriginal.set(item.clave, item.valor));
    userOriginalValues.value = nextOriginal;

    const themeOverride = map.get('tema.modo')?.valor;
    if (themeOverride && !isThemeGlobal(themeOverride)) {
      applyThemeValue(themeOverride);
    }
    const densityOverride = map.get('ui.densidad')?.valor;
    if (densityOverride) {
      applyDensityValue(densityOverride);
    }
    const iconOverride = map.get('ui.sidebar_icon')?.valor;
    if (iconOverride) {
      window.dispatchEvent(new CustomEvent('bitacora:user-pref', { detail: { clave: 'ui.sidebar_icon', valor: iconOverride } }));
    }
  } catch (error) {
    console.error('Error al cargar configuracion de usuario', error);
  }
};

const saveItem = async (item) => {
  if (!isDirty(item) || isSaving(item)) {
    return;
  }

  const previous = item.valor;
  const nextSaving = new Set(savingIds.value);
  nextSaving.add(item.id);
  savingIds.value = nextSaving;
  errorById.value = { ...errorById.value, [item.id]: '' };

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.put(`/api/v1/admin/configuracion/sistema/${item.id}`, {
      valor: String(item.valor ?? ''),
    });

    const updated = response.data?.data ?? response.data;
    if (updated?.valor !== undefined) {
      item.valor = updated.valor;
    }

    const nextOriginal = new Map(originalValues.value);
    nextOriginal.set(item.id, item.valor);
    originalValues.value = nextOriginal;

    const nextSaved = new Set(savedIds.value);
    nextSaved.add(item.id);
    savedIds.value = nextSaved;

    if (item.clave === 'tema.modo') {
      if (isThemeGlobal(userThemeItem.value?.valor)) {
        applyThemeValue(item.valor);
      }
    }
  } catch (error) {
    item.valor = previous;
    errorById.value = {
      ...errorById.value,
      [item.id]: 'No se pudo guardar. Verifica permisos o intenta de nuevo.',
    };
    console.error('Error al guardar configuracion', error);
  } finally {
    const next = new Set(savingIds.value);
    next.delete(item.id);
    savingIds.value = next;
  }
};

const saveUserItem = async (item) => {
  if (!item || !isUserDirty(item) || isUserSaving(item)) {
    return;
  }

  const previous = item.valor;
  const nextSaving = new Set(userSavingKeys.value);
  nextSaving.add(item.clave);
  userSavingKeys.value = nextSaving;
  userErrorByKey.value = { ...userErrorByKey.value, [item.clave]: '' };

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const payload = {
      clave: item.clave,
      valor: String(item.valor ?? ''),
      tipo: item.tipo || 'string',
      descripcion: item.descripcion || null,
    };

    let response;
    if (item.id) {
      response = await window.axios.put(`/api/v1/configuracion/usuario/${item.id}`, payload);
    } else {
      response = await window.axios.post('/api/v1/configuracion/usuario', payload);
    }

    const updated = response.data?.data ?? response.data;
    if (updated?.id) {
      item.id = updated.id;
    }
    if (updated?.valor !== undefined) {
      item.valor = updated.valor;
    }

    const nextOriginal = new Map(userOriginalValues.value);
    nextOriginal.set(item.clave, item.valor);
    userOriginalValues.value = nextOriginal;

    const nextSaved = new Set(userSavedKeys.value);
    nextSaved.add(item.clave);
    userSavedKeys.value = nextSaved;

    if (item.clave === 'tema.modo') {
      if (isThemeGlobal(item.valor)) {
        applyThemeValue(resolveSystemTheme());
      } else {
        applyThemeValue(item.valor);
      }
    }

    handleUserChange(item);
  } catch (error) {
    item.valor = previous;
    userErrorByKey.value = {
      ...userErrorByKey.value,
      [item.clave]: 'No se pudo guardar. Verifica permisos o intenta de nuevo.',
    };
    console.error('Error al guardar configuracion usuario', error);
  } finally {
    const next = new Set(userSavingKeys.value);
    next.delete(item.clave);
    userSavingKeys.value = next;
  }
};

const resetPasswordForm = () => {
  passwordForm.current_password = '';
  passwordForm.password = '';
  passwordForm.password_confirmation = '';
};

const clearPasswordErrors = () => {
  passwordErrors.current_password = '';
  passwordErrors.password = '';
  passwordErrors.password_confirmation = '';
};

const validatePasswordForm = () => {
  let hasErrors = false;
  clearPasswordErrors();

  const current = String(passwordForm.current_password || '');
  const password = String(passwordForm.password || '');
  const confirmation = String(passwordForm.password_confirmation || '');

  if (!current) {
    passwordErrors.current_password = 'Ingresa tu contraseña actual.';
    hasErrors = true;
  }

  if (!password) {
    passwordErrors.password = 'Ingresa la nueva contraseña.';
    hasErrors = true;
  }

  if (!confirmation) {
    passwordErrors.password_confirmation = 'Confirma la nueva contraseña.';
    hasErrors = true;
  }

  if (password && confirmation && password !== confirmation) {
    passwordErrors.password_confirmation = 'La confirmación no coincide.';
    hasErrors = true;
  }

  if (current && password && current === password) {
    passwordErrors.password = 'La nueva contraseña debe ser distinta a la actual.';
    hasErrors = true;
  }

  if (hasErrors) {
    passwordStatusType.value = 'error';
    passwordStatusMessage.value = 'Revisa los campos marcados en rojo.';
    return false;
  }

  return true;
};

const updatePassword = async () => {
  if (passwordSaving.value) {
    return;
  }

  passwordStatusMessage.value = '';
  passwordStatusType.value = '';

  if (!validatePasswordForm()) {
    return;
  }

  passwordSaving.value = true;

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.put('/api/v1/configuracion/usuario/password', {
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    });

    passwordStatusType.value = 'success';
    passwordStatusMessage.value = response.data?.message || 'Contraseña actualizada correctamente.';
    resetPasswordForm();
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    clearPasswordErrors();
    passwordErrors.current_password = errors.current_password?.[0] || '';
    passwordErrors.password = errors.password?.[0] || '';
    passwordErrors.password_confirmation = errors.password_confirmation?.[0] || '';

    passwordStatusType.value = 'error';
    if (!Object.keys(errors).length) {
      passwordStatusMessage.value = error.response?.data?.message || 'No se pudo actualizar la contraseña.';
    } else {
      passwordStatusMessage.value = 'No se pudo actualizar la contraseña. Verifica los datos.';
    }
  } finally {
    passwordSaving.value = false;
  }
};

const validateCleanupHours = () => {
  const hours = Number(cleanupForm.hours);
  if (!Number.isFinite(hours) || hours < 1 || hours > 720) {
    cleanupStatusType.value = 'error';
    cleanupStatusMessage.value = 'La antigüedad debe ser un entero entre 1 y 720 horas.';
    return null;
  }
  return Math.trunc(hours);
};

const validateImportCleanupHours = () => {
  const hours = Number(importCleanupForm.hours);
  if (!Number.isFinite(hours) || hours < 1 || hours > 720) {
    importCleanupStatusType.value = 'error';
    importCleanupStatusMessage.value = 'La antigüedad debe ser un entero entre 1 y 720 horas.';
    return null;
  }
  return Math.trunc(hours);
};

const runAdjuntosCleanup = async (dryRun = true) => {
  if (cleanupLoading.value) {
    return;
  }

  cleanupStatusMessage.value = '';
  cleanupStatusType.value = '';

  const hours = validateCleanupHours();
  if (hours === null) {
    return;
  }

  if (!dryRun) {
    const accepted = window.confirm(
      `Se eliminarán adjuntos temporales (sin entrada) y archivos huérfanos en disco con más de ${hours} hora(s). ¿Deseas continuar?`
    );
    if (!accepted) {
      return;
    }
  }

  cleanupLoading.value = true;

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post('/api/v1/admin/mantenimiento/adjuntos-temporales/cleanup', {
      hours,
      dry_run: dryRun,
    });

    const payload = response.data?.data ?? response.data ?? {};
    const total = Number(payload.total ?? 0);
    const deleted = Number(payload.deleted ?? 0);
    const dbTotal = Number(payload.db_temporales_total ?? 0);
    const fsTotal = Number(payload.fs_huerfanos_total ?? 0);
    const dbDeleted = Number(payload.db_temporales_deleted ?? 0);
    const fsDeleted = Number(payload.fs_huerfanos_deleted ?? 0);

    cleanupStatusType.value = 'success';
    cleanupStatusMessage.value = dryRun
      ? `Simulación: ${total} candidato(s) para limpieza (DB temporales: ${dbTotal}, huérfanos en disco: ${fsTotal}).`
      : `Limpieza completada: ${deleted} candidato(s) eliminado(s) de ${total} (DB temporales: ${dbDeleted}, huérfanos en disco: ${fsDeleted}).`;
  } catch (error) {
    cleanupStatusType.value = 'error';
    cleanupStatusMessage.value = error.response?.data?.message || 'No se pudo ejecutar la limpieza de adjuntos temporales.';
    console.error('Error al limpiar adjuntos temporales', error);
  } finally {
    cleanupLoading.value = false;
  }
};

const runImportFilesCleanup = async (dryRun = true) => {
  if (importCleanupLoading.value) {
    return;
  }

  importCleanupStatusMessage.value = '';
  importCleanupStatusType.value = '';

  const hours = validateImportCleanupHours();
  if (hours === null) {
    return;
  }

  if (!dryRun) {
    const accepted = window.confirm(
      `Se eliminarán archivos de importación en storage/app/imports con más de ${hours} hora(s). ¿Deseas continuar?`
    );
    if (!accepted) {
      return;
    }
  }

  importCleanupLoading.value = true;

  try {
    await window.axios.get('/sanctum/csrf-cookie');
    const response = await window.axios.post('/api/v1/admin/mantenimiento/importaciones-archivos/cleanup', {
      hours,
      dry_run: dryRun,
    });

    const payload = response.data?.data ?? response.data ?? {};
    const total = Number(payload.total ?? 0);
    const deleted = Number(payload.deleted ?? 0);
    const dbFilesTotal = Number(payload.db_import_archivos_total ?? 0);
    const dbRowsTotal = Number(payload.db_import_registros_afectados ?? 0);
    const fsTotal = Number(payload.fs_huerfanos_total ?? 0);
    const dbFilesDeleted = Number(payload.db_import_archivos_deleted ?? 0);
    const dbRowsUpdated = Number(payload.db_import_registros_actualizados ?? 0);
    const fsDeleted = Number(payload.fs_huerfanos_deleted ?? 0);

    importCleanupStatusType.value = 'success';
    importCleanupStatusMessage.value = dryRun
      ? `Simulación: ${total} archivo(s) candidato(s) (referenciados: ${dbFilesTotal}, huérfanos: ${fsTotal}, filas importación afectadas: ${dbRowsTotal}).`
      : `Limpieza completada: ${deleted} archivo(s) eliminado(s) de ${total} (referenciados: ${dbFilesDeleted}, huérfanos: ${fsDeleted}, filas importación actualizadas: ${dbRowsUpdated}).`;
  } catch (error) {
    importCleanupStatusType.value = 'error';
    importCleanupStatusMessage.value = error.response?.data?.message || 'No se pudo ejecutar la limpieza de archivos de importación.';
    console.error('Error al limpiar archivos de importación', error);
  } finally {
    importCleanupLoading.value = false;
  }
};

onMounted(() => {
  loadUserRole();
  loadData();
});

watch(temaModo, (value) => {
  if (value) {
    if (isThemeGlobal(userThemeItem.value?.valor)) {
      applyThemeValue(value);
    }
  }
});

watch(isAdmin, () => {
  syncActiveTab();
});

watch(
  () => [passwordForm.current_password, passwordForm.password, passwordForm.password_confirmation],
  () => {
    if (passwordStatusMessage.value) {
      passwordStatusMessage.value = '';
      passwordStatusType.value = '';
    }
  }
);

watch(
  () => cleanupForm.hours,
  () => {
    if (cleanupStatusMessage.value) {
      cleanupStatusMessage.value = '';
      cleanupStatusType.value = '';
    }
  }
);

watch(
  () => importCleanupForm.hours,
  () => {
    if (importCleanupStatusMessage.value) {
      importCleanupStatusMessage.value = '';
      importCleanupStatusType.value = '';
    }
  }
);
</script>
