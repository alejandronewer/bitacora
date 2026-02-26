<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark text-slate-900 dark:text-white">
    <header class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-[#233648] bg-background-light dark:bg-background-dark px-4 md:px-10 py-3">
      <div class="max-w-[1440px] mx-auto flex items-center justify-between gap-8">
        <div class="flex items-center gap-3">
          <div class="size-8 text-primary">
            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
              <path d="M4 4H17.3334V17.3334H30.6666V30.6666H44V44H4V4Z" fill="currentColor"></path>
            </svg>
          </div>
          <h2 v-if="appNombre" class="text-lg font-bold leading-tight tracking-tight hidden md:block">{{ appNombre }}</h2>
        </div>
        <div class="flex items-center gap-4">
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

    <main class="max-w-[1100px] mx-auto p-4 md:p-8 space-y-6">
      <div class="flex items-center gap-2 text-sm">
        <router-link class="text-[#92adc9] hover:text-primary transition-colors" to="/timeline">Línea de Tiempo</router-link>
        <span class="text-[#92adc9]">/</span>
        <span class="font-medium">Detalle de entrada</span>
      </div>
      <div class="flex items-center justify-between">
        <router-link
          class="group inline-flex items-center gap-3 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary shadow-sm shadow-primary/10 transition-all hover:-translate-y-0.5 hover:border-primary/40 hover:bg-primary/15 hover:shadow-lg hover:shadow-primary/20"
          to="/timeline"
        >
          <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary shadow-sm group-hover:-translate-x-0.5 transition-transform">
            <span class="material-symbols-outlined text-base">arrow_back</span>
          </span>
          Volver a la línea de tiempo
        </router-link>
      </div>

      <div v-if="loading" class="space-y-4">
        <div class="h-6 w-48 rounded bg-slate-200/70 dark:bg-border-dark/60 animate-pulse"></div>
        <div class="h-10 w-3/4 rounded bg-slate-200/70 dark:bg-border-dark/60 animate-pulse"></div>
        <div class="h-4 w-1/2 rounded bg-slate-200/70 dark:bg-border-dark/60 animate-pulse"></div>
        <div class="h-40 w-full rounded-xl bg-slate-200/70 dark:bg-border-dark/60 animate-pulse"></div>
      </div>

      <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
      </div>

      <section v-if="entrada" class="bg-white dark:bg-[#16222e] rounded-2xl border border-slate-200 dark:border-[#233648] p-6 md:p-7">
        <div class="space-y-6">
          <div class="flex flex-col gap-4">
            <div class="flex flex-wrap gap-2">
              <span
                v-if="authUser"
                class="px-2 py-0.5 rounded bg-green-500/10 text-green-500 text-[10px] font-bold border border-green-500/20 uppercase tracking-wide"
              >
                Publicado
              </span>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
              <div>
                <h1 class="text-2xl md:text-3xl font-black leading-tight">
                  {{ entrada.titulo || 'Entrada sin título' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-[#92adc9] mt-2">
                  {{ fechaLabel }} · Iniciado {{ horaInicio || 'sin hora registrada' }}
                  <span v-if="horaFin"> · Finalizado {{ horaFin }}</span>
                </p>
              </div>
              <button
                v-if="canEditar"
                class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-primary/20"
                @click="goToEdit"
              >
                <span class="material-symbols-outlined text-base">edit</span>
                Editar entrada
              </button>
            </div>
          </div>

          <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex-1 space-y-6">

            <div v-if="entrada.resumen_tecnico" class="rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/80 dark:bg-border-dark/60 p-4">
              <div class="flex items-center justify-between gap-2 mb-2">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9]">Resumen técnico</h3>
              </div>
              <pre class="whitespace-pre-wrap text-sm text-slate-700 dark:text-[#92adc9] leading-relaxed">{{ entrada.resumen_tecnico }}</pre>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-white/80 dark:bg-[#16222e]/60 p-4">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9] mb-3">Detalle</h3>
              <div class="rich-content text-sm text-slate-700 dark:text-[#92adc9] leading-relaxed" v-html="entrada.cuerpo_html"></div>
            </div>

            <div v-if="inventarioElementos.length" class="rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/80 dark:bg-border-dark/60 p-4">
              <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9]">Elementos de inventario asignados</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                  {{ inventarioElementos.length }}
                </span>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <article
                  v-for="elemento in inventarioElementos"
                  :key="elemento.id"
                  class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-[#16222e] p-3"
                >
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ elemento.nombre || elemento.codigo || `Elemento #${elemento.id}` }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">
                    {{ elemento.codigo || 'Sin código' }}
                  </p>
                  <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border border-slate-200 dark:border-border-dark text-slate-600 dark:text-[#92adc9]">
                      {{ formatTipoInventario(elemento.tipo) }}
                    </span>
                    <span
                      v-if="elemento.red?.nombre"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border border-slate-200 dark:border-border-dark text-slate-600 dark:text-[#92adc9]"
                    >
                      {{ elemento.red.nombre }}
                    </span>
                    <div v-if="hasExtensionTable(elemento.tipo)" class="relative group">
                      <button
                        type="button"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-primary/20 text-primary bg-primary/10 cursor-help"
                      >
                        <span class="material-symbols-outlined text-xs">info</span>
                        <span>Detalle</span>
                      </button>
                      <div
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
                </article>
              </div>
            </div>

            <div
              v-else-if="entrada.tipo_registro === 'inventario'"
              class="rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/80 dark:bg-border-dark/60 p-4 text-sm text-slate-600 dark:text-[#92adc9]"
            >
              Esta entrada no tiene elementos de inventario asignados.
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/80 dark:bg-border-dark/60 p-4">
              <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9]">
                  Equipos SAP-like asignados
                </h3>
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                    Equipos: {{ equiposSapLike.length }}
                  </span>
                </div>
              </div>
              <div v-if="equiposSapLike.length || ubicacionesSapLike.length" class="space-y-3">
                <div v-if="ubicacionesSapLike.length" class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-[#16222e] p-3">
                  <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#92adc9] mb-2">
                    Ubicaciones técnicas relacionadas ({{ ubicacionesSapLike.length }})
                  </p>
                  <div class="flex flex-wrap gap-2">
                    <div
                      v-for="ubicacion in ubicacionesSapLike"
                      :key="ubicacion.id"
                      class="inline-flex items-center rounded-full text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20"
                    >
                      <UbicacionCodigoTooltip
                        v-if="ubicacion.codigo"
                        :ubicacion="ubicacion"
                        :resolve-detalle="resolveDetalleNivelUbicacion"
                      />
                      <span v-else class="px-2.5 py-1">{{ `Ubicación #${ubicacion.id}` }}</span>
                    </div>
                  </div>
                </div>
                <div v-if="equiposSapLike.length" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <article
                    v-for="equipo in equiposSapLike"
                    :key="equipo.id"
                    class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-[#16222e] p-3 shadow-sm"
                  >
                    <div class="flex items-center justify-between gap-2">
                      <p class="text-sm font-bold text-slate-900 dark:text-white">
                        {{ equipo.codigo || `Equipo #${equipo.id}` }}
                      </p>
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border border-slate-200 dark:border-border-dark text-slate-600 dark:text-[#92adc9]">
                        {{ equipo.area || 'Sin área' }}
                      </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-[#92adc9] mt-1.5">
                      {{ equipo.nombre || 'Sin nombre' }}
                    </p>
                    <div class="mt-2 flex items-center gap-1.5">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-border-dark/50 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]">
                        UT
                      </span>
                      <UbicacionCodigoTooltip
                        v-if="resolveEquipoUbicacion(equipo)?.codigo"
                        :ubicacion="resolveEquipoUbicacion(equipo)"
                        :resolve-detalle="resolveDetalleNivelUbicacion"
                      />
                      <span
                        v-else
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-border-dark/50 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]"
                      >
                        Sin ubicación
                      </span>
                    </div>
                  </article>
                </div>
                <p v-else class="text-sm text-slate-600 dark:text-[#92adc9]">
                  Sin equipos asignados.
                </p>
              </div>
              <p v-else class="text-sm text-slate-600 dark:text-[#92adc9]">
                Esta entrada no tiene ubicaciones técnicas ni equipos SAP-like asignados.
              </p>
            </div>

            <div v-if="adjuntosImagen.length" class="space-y-2">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9]">Imágenes adjuntas</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <button
                  v-for="img in adjuntosImagen"
                  :key="img.id"
                  type="button"
                  class="group overflow-hidden rounded-lg border border-slate-200 dark:border-border-dark"
                  @click="openImagePreview(img)"
                >
                  <img
                    :src="img._url"
                    :alt="img.nombre_original || 'Imagen adjunta'"
                    class="h-40 w-full object-cover transition-transform group-hover:scale-105"
                  />
                </button>
              </div>
            </div>

            <div v-if="adjuntosArchivo.length" class="space-y-2">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9]">Archivos adjuntos</h3>
              <ul class="space-y-2 text-sm text-slate-600 dark:text-[#92adc9]">
                <li v-for="file in adjuntosArchivo" :key="file.id">
                  <a class="text-primary hover:underline" :href="file._url" target="_blank" rel="noopener">
                    {{ file.nombre_original || file.ruta }}
                  </a>
                </li>
              </ul>
            </div>
          </div>

          <aside class="w-full lg:w-80 shrink-0 space-y-4">
            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-white/80 dark:bg-[#16222e]/60 p-4">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9] mb-3">Autor</h3>
              <div class="text-sm text-slate-700 dark:text-white font-semibold">
                {{ entrada.usuario?.nombre || 'No definido' }}
              </div>
              <div v-if="entrada.usuario?.correo" class="text-xs text-slate-500 dark:text-[#92adc9] mt-1">
                {{ entrada.usuario.correo }}
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/70 dark:bg-border-dark/60 p-4">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9] mb-3">Clasificación</h3>
              <dl class="space-y-2 text-sm text-slate-600 dark:text-[#92adc9]">
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Criterio</dt>
                  <dd class="text-right">{{ entrada.criterio?.nombre || 'No definido' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Impacto</dt>
                  <dd class="text-right">{{ entrada.impacto?.nombre || 'No definido' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Evento</dt>
                  <dd class="text-right">{{ entrada.evento_detectado?.tipo_evento || 'No aplica' }}</dd>
                </div>
              </dl>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-white/80 dark:bg-[#16222e]/60 p-4">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9] mb-3">Orden de actividad</h3>
              <dl class="space-y-2 text-sm text-slate-600 dark:text-[#92adc9]">
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Orden</dt>
                  <dd class="text-right">{{ entrada.pm_clase_orden?.nombre || 'No disponible' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Actividad</dt>
                  <dd class="text-right">{{ entrada.pm_clase_actividad?.nombre || 'No disponible' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Tipo</dt>
                  <dd>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border" :class="badgeClass">
                      {{ badgeLabel }}
                    </span>
                  </dd>
                </div>
              </dl>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50/70 dark:bg-border-dark/60 p-4">
              <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9] mb-3">Fechas</h3>
              <dl class="space-y-2 text-sm text-slate-600 dark:text-[#92adc9]">
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Inicio</dt>
                  <dd class="text-right">{{ fechaInicioDetalle }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                  <dt class="font-semibold text-slate-700 dark:text-white">Fin</dt>
                  <dd class="text-right">{{ fechaFinDetalle }}</dd>
                </div>
              </dl>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-border-dark bg-white/80 dark:bg-[#16222e]/60 p-4">
              <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-[#92adc9]">
                  Referencias externas
                </h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                  {{ referenciasExternas.length }}
                </span>
              </div>
              <div v-if="referenciasExternas.length" class="flex flex-wrap gap-2">
                <span
                  v-for="ref in referenciasExternas"
                  :key="ref.id"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-border-dark/50 border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#d4e4f6]"
                  :title="ref.sistema?.nombre || 'Sistema externo'"
                >
                  <span class="text-slate-500 dark:text-[#92adc9]">
                    {{ ref.sistema?.codigo || `Sistema ${ref.sistema_externo_id}` }}
                  </span>
                  <span class="text-slate-400 dark:text-[#5f7f9f]">·</span>
                  <span>{{ ref.externo_id }}</span>
                </span>
              </div>
              <p v-else class="text-sm text-slate-600 dark:text-[#92adc9]">
                Sin referencias externas registradas.
              </p>
            </div>
          </aside>
        </div>
      </div>
      </section>
      <div v-if="entrada" class="flex flex-col md:flex-row items-center justify-between gap-4 rounded-xl border border-slate-200 dark:border-[#233648] bg-white/80 dark:bg-[#16222e] px-4 py-3">
        <router-link
          class="inline-flex items-center gap-2 rounded-lg bg-primary text-white px-3 py-2 text-sm font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors"
          to="/timeline"
        >
          Línea de tiempo
        </router-link>
        <div class="flex items-center gap-3">
          <button
            v-if="canEditar"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-primary/30 text-primary px-3 py-2 text-sm font-semibold hover:bg-primary/10 transition-colors"
            @click="goToEdit"
          >
            <span class="material-symbols-outlined text-base">edit</span>
            Editar
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-primary text-white px-3 py-2 text-sm font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary"
            :disabled="!hasPrev"
            @click="goToPrevious"
          >
            <span class="material-symbols-outlined text-base">chevron_left</span>
            Entrada anterior
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-primary text-white px-3 py-2 text-sm font-semibold shadow-md shadow-primary/15 hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary"
            :disabled="!hasNext"
            @click="goToNext"
          >
            Entrada siguiente
            <span class="material-symbols-outlined text-base">chevron_right</span>
          </button>
        </div>
      </div>

      <div
        v-if="imagePreviewOpen"
        class="fixed inset-0 z-[70] bg-slate-950/80 p-4 flex items-center justify-center"
        @click="closeImagePreview"
      >
        <div class="relative max-w-6xl w-full" @click.stop>
          <button
            type="button"
            class="absolute right-3 top-3 inline-flex size-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
            @click="closeImagePreview"
          >
            <span class="material-symbols-outlined text-base">close</span>
          </button>
          <img
            :src="imagePreviewSrc"
            :alt="imagePreviewAlt"
            class="max-h-[86vh] w-full object-contain rounded-xl border border-slate-700/60 bg-slate-900"
          />
        </div>
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
import { useRoute, useRouter } from 'vue-router';
import { fetchConfiguracionSistema } from '../api/configuracion';
import UbicacionCodigoTooltip from '../components/UbicacionCodigoTooltip.vue';

const route = useRoute();
const router = useRouter();

const entrada = ref(null);
const loading = ref(false);
const error = ref('');
const appNombre = ref('');
const authUser = ref(null);
const ubicacionDetalleNivelesCatalogo = ref([]);
const imagePreviewOpen = ref(false);
const imagePreviewSrc = ref('');
const imagePreviewAlt = ref('Imagen adjunta');
const links = ref({
  gtrbc: '#',
  subger: '#',
});

const badgeLabel = computed(() => (entrada.value?.tipo_registro === 'inventario' ? 'INVENTARIO' : 'OPERATIVO'));
const badgeClass = computed(() =>
  entrada.value?.tipo_registro === 'inventario'
    ? 'bg-blue-500/10 text-blue-500 border-blue-500/20'
    : 'bg-amber-500/10 text-amber-500 border-amber-500/20'
);

const fechaLabel = computed(() => {
  if (!entrada.value?.fecha_inicio) return 'Fecha no definida';
  const date = new Date(entrada.value.fecha_inicio);
  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'long' }).format(date);
});

const horaInicio = computed(() => {
  if (!entrada.value?.fecha_inicio) return null;
  const date = new Date(entrada.value.fecha_inicio);
  return new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit' }).format(date);
});

const horaFin = computed(() => {
  if (!entrada.value?.fecha_fin) return null;
  const date = new Date(entrada.value.fecha_fin);
  return new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit' }).format(date);
});

const formatFechaDetalle = (value) => {
  if (!value) return 'Sin fecha registrada';
  const date = new Date(value);
  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};

const fechaInicioDetalle = computed(() => formatFechaDetalle(entrada.value?.fecha_inicio));
const fechaFinDetalle = computed(() => formatFechaDetalle(entrada.value?.fecha_fin));

const adjuntosList = computed(() => {
  const raw = entrada.value?.adjuntos;
  if (Array.isArray(raw)) return raw;
  if (raw && Array.isArray(raw.data)) return raw.data;
  return [];
});

const resolveAdjuntoUrl = (adj) => {
  if (!adj) return null;
  if (adj.url) return adj.url;
  if (!adj.ruta) return null;
  const ruta = String(adj.ruta).replace(/^\/+/, '');
  return ruta.startsWith('storage/') ? `/${ruta}` : `/storage/${ruta}`;
};

const isImagenAdjunto = (adj) => {
  const tipo = String(adj?.tipo || '').toLowerCase();
  if (tipo) return tipo === 'imagen';
  const mime = String(adj?.mime_final || adj?.mime_original || '').toLowerCase();
  return mime.startsWith('image/');
};

const adjuntosImagen = computed(() =>
  adjuntosList.value
    .filter((adj) => isImagenAdjunto(adj))
    .map((adj) => ({ ...adj, _url: resolveAdjuntoUrl(adj) }))
    .filter((adj) => adj._url)
);

const adjuntosArchivo = computed(() =>
  adjuntosList.value
    .filter((adj) => !isImagenAdjunto(adj))
    .map((adj) => ({ ...adj, _url: resolveAdjuntoUrl(adj) }))
    .filter((adj) => adj._url)
);

const inventarioElementos = computed(() => {
  const raw = entrada.value?.inventario_elementos;
  if (Array.isArray(raw)) return raw;
  if (raw && Array.isArray(raw.data)) return raw.data;
  return [];
});

const referenciasExternas = computed(() => {
  const raw = entrada.value?.referencias_externas;
  if (Array.isArray(raw)) return raw;
  if (raw && Array.isArray(raw.data)) return raw.data;
  return [];
});

const normalizeCollection = (raw) => {
  if (Array.isArray(raw)) return raw;
  if (raw && Array.isArray(raw.data)) return raw.data;
  return [];
};

const equiposSapLike = computed(() => {
  const fromPivot = normalizeCollection(entrada.value?.equipos);
  const fromDirect = entrada.value?.equipo ? [entrada.value.equipo] : [];
  const merged = [...fromPivot, ...fromDirect].filter(Boolean);

  const byKey = new Map();
  for (const equipo of merged) {
    const key = equipo?.id != null
      ? `id:${equipo.id}`
      : (equipo?.codigo ? `codigo:${equipo.codigo}` : null);
    if (!key || byKey.has(key)) continue;
    byKey.set(key, equipo);
  }

  return Array.from(byKey.values());
});

const ubicacionesSapLike = computed(() => {
  const fromPivot = normalizeCollection(entrada.value?.ubicaciones);
  const fromDirect = entrada.value?.ubicacion_tecnica ? [entrada.value.ubicacion_tecnica] : [];
  const fromEquipos = equiposSapLike.value
    .map((equipo) => equipo?.ubicacion_tecnica)
    .filter(Boolean);
  const merged = [...fromPivot, ...fromDirect, ...fromEquipos];

  const byKey = new Map();
  for (const ubicacion of merged) {
    const key = ubicacion?.id != null
      ? `id:${ubicacion.id}`
      : (ubicacion?.codigo ? `codigo:${ubicacion.codigo}` : null);
    if (!key || byKey.has(key)) continue;
    byKey.set(key, ubicacion);
  }

  return Array.from(byKey.values());
});

const ubicacionPorId = computed(() => {
  const map = new Map();
  for (const ubicacion of ubicacionesSapLike.value) {
    map.set(Number(ubicacion.id), ubicacion);
  }
  return map;
});

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

const resolveEquipoUbicacion = (equipo) => {
  if (!equipo) return null;
  return ubicacionPorId.value.get(Number(equipo.ubicacion_tecnica_id))
    || equipo.ubicacion_tecnica
    || null;
};

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

const canEditar = computed(() => {
  const roles = authUser.value?.roles || [];
  return roles.includes('administrador') || roles.includes('operador');
});

const goToEdit = () => {
  if (!entrada.value?.id) return;
  router.push({ name: 'entrada-editar', params: { id: entrada.value.id } });
};

const hasPrev = computed(() => Boolean(entrada.value?.prev_id));
const hasNext = computed(() => Boolean(entrada.value?.next_id));

const goToPrevious = () => {
  if (!hasPrev.value) return;
  router.push({ name: 'entrada-show', params: { id: entrada.value.prev_id } });
};

const goToNext = () => {
  if (!hasNext.value) return;
  router.push({ name: 'entrada-show', params: { id: entrada.value.next_id } });
};

const openImagePreview = (img) => {
  if (!img?._url) return;
  imagePreviewSrc.value = img._url;
  imagePreviewAlt.value = img.nombre_original || 'Imagen adjunta';
  imagePreviewOpen.value = true;
};

const closeImagePreview = () => {
  imagePreviewOpen.value = false;
  imagePreviewSrc.value = '';
  imagePreviewAlt.value = 'Imagen adjunta';
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

const loadAuthUser = async () => {
  try {
    const response = await window.axios.get('/api/v1/me', { skipAuthRedirect: true });
    authUser.value = response.data?.data ?? response.data;
  } catch {
    authUser.value = null;
  }
};

const loadUbicacionDetalleNivelesCatalogo = async () => {
  try {
    const response = await window.axios.get('/api/v1/ubicaciones/detalle-niveles', { skipAuthRedirect: true });
    ubicacionDetalleNivelesCatalogo.value = response.data?.data ?? response.data ?? [];
  } catch {
    ubicacionDetalleNivelesCatalogo.value = [];
  }
};

const loadEntrada = async () => {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  error.value = '';
  try {
    const response = await window.axios.get(`/api/v1/entradas/${id}`, { skipAuthRedirect: true });
    entrada.value = response.data?.data ?? response.data;
    closeImagePreview();
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudo cargar la entrada.';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await Promise.all([
    loadAuthUser(),
    loadConfig(),
    loadUbicacionDetalleNivelesCatalogo(),
    loadEntrada(),
  ]);
});

watch(
  () => route.params.id,
  async () => {
    await loadEntrada();
  }
);
</script>
