                                                                                                            <template>
  <aside class="w-72 bg-white dark:bg-surface-dark border-r border-slate-200 dark:border-border-dark flex flex-col shrink-0">
    <div class="p-6 flex flex-col gap-8 h-full">
      <div class="flex items-center gap-3">
        <div class="bg-primary rounded-lg p-2 text-white">
          <span class="material-symbols-outlined">settings_input_antenna</span>
        </div>
        <div>
          <h1 class="text-lg font-bold leading-tight">{{ appSiglas }}</h1>
          <p class="text-slate-500 dark:text-[#92adc9] text-xs">Menú de Navegación</p>
        </div>
      </div>

      <nav class="flex flex-col gap-1 overflow-y-auto grow">
        <RouterLink class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors" :class="linkClass('/dashboard')" to="/dashboard">
          <span class="material-symbols-outlined">dashboard</span>
          <span class="text-sm font-medium">Dashboard</span>
        </RouterLink>
        <RouterLink
          v-if="canCreateEntradas"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/entradas/nueva')"
          to="/entradas/nueva"
        >
          <span class="material-symbols-outlined">post_add</span>
          <span class="text-sm font-medium">Nueva Entrada</span>
        </RouterLink>
        <RouterLink
          v-if="canCreateEntradas"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/entradas/gestion')"
          to="/entradas/gestion"
        >
          <span class="material-symbols-outlined">list_alt</span>
          <span class="text-sm font-medium">Gestión de Entradas</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/usuarios-roles')"
          to="/usuarios-roles"
        >
          <span class="material-symbols-outlined">group</span>
          <span class="text-sm font-medium">Usuarios y Roles</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/catalogos-tecnicos')"
          to="/catalogos-tecnicos"
        >
          <span class="material-symbols-outlined">inventory_2</span>
          <span class="text-sm font-medium">Catálogos Técnicos</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/sistemas-externos')"
          to="/sistemas-externos"
        >
          <span class="material-symbols-outlined">link</span>
          <span class="text-sm font-medium">Sistemas Externos</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/inventario-redes')"
          to="/inventario-redes"
        >
          <span class="material-symbols-outlined">lan</span>
          <span class="text-sm font-medium">Inventario NMS/EMS</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/importaciones-reglas')"
          to="/importaciones-reglas"
        >
          <span class="material-symbols-outlined">upload_file</span>
          <span class="text-sm font-medium">Reglas de Importación</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/ubicaciones-equipos')"
          to="/ubicaciones-equipos"
        >
          <span class="material-symbols-outlined">location_on</span>
          <span class="text-sm font-medium">Ubic. Técnicas y Equipos</span>
        </RouterLink>
        <RouterLink
          v-if="isAdmin"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/pm-matriz')"
          to="/pm-matriz"
        >
          <span class="material-symbols-outlined">inventory</span>
          <span class="text-sm font-medium">Catálogo de Mantenimientos</span>
        </RouterLink>
        <RouterLink
          v-if="canSeeConfig"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
          :class="linkClass('/configuracion')"
          to="/configuracion"
        >
          <span class="material-symbols-outlined">settings</span>
          <span class="text-sm font-medium">Configuración</span>
        </RouterLink>
      </nav>

      <UserBadge :user="user" :on-logout="onLogout" :icon="userIcon" />
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import UserBadge from './UserBadge.vue';

const route = useRoute();

const linkClass = (target) => {
  const isActive = route.path === target;
  return isActive
    ? 'bg-primary text-white shadow-lg shadow-primary/20'
    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-border-dark';
};

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  appSiglas: {
    type: String,
    default: 'COReFO B.C.',
  },
  userIcon: {
    type: String,
    default: 'account_circle',
  },
  onLogout: {
    type: Function,
    required: false,
  },
});

const roleNames = computed(() =>
  (props.user?.roles || [])
    .map((role) => (typeof role === 'string' ? role : role?.name))
    .filter(Boolean)
    .map((role) => role.toLowerCase())
);

const isAdmin = computed(() => roleNames.value.includes('administrador'));
const isOperador = computed(() => roleNames.value.includes('operador'));
const canCreateEntradas = computed(() => isAdmin.value || isOperador.value);
const canSeeConfig = computed(() => isAdmin.value || isOperador.value);
</script>
