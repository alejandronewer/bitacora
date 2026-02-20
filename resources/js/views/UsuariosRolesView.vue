<template>
  <DashboardLayout>
    <div class="space-y-6">
      <nav class="flex items-center gap-2 text-sm">
        <a class="text-slate-500 dark:text-[#92adc9] hover:text-primary transition-colors" href="#">Administración</a>
        <span class="material-symbols-outlined text-xs text-slate-400">chevron_right</span>
        <span class="font-semibold text-slate-900 dark:text-white">Usuarios y Roles</span>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Usuarios y Roles</h3>
          <p class="text-slate-500 dark:text-[#92adc9] mt-1">Gestión de usuarios, roles y estado de cuentas.</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-primary/25"
            @click="openCreate"
          >
            <span class="material-symbols-outlined">person_add</span>
            <span>Nuevo Usuario</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
            <h4 class="font-bold text-lg">Usuarios</h4>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ filteredUsuarios.length }} registros
            </span>
          </div>
          <div class="px-6 pb-4 flex flex-wrap items-center gap-2">
            <input
              v-model="userQuery"
              type="text"
              class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm"
              placeholder="Buscar usuario..."
            />
            <select v-model="userStatus" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos</option>
              <option value="activo">Activos</option>
              <option value="inactivo">Inactivos</option>
            </select>
            <select v-model="userRole" class="bg-slate-50 dark:bg-border-dark/60 border border-slate-200 dark:border-border-dark rounded-lg px-3 py-1.5 text-sm">
              <option value="">Todos los roles</option>
              <option value="Invitado">Invitado</option>
              <option v-for="role in adminRoles" :key="role.id" :value="role.name">
                {{ role.name }}
              </option>
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
                  <th class="px-6 py-4">Usuario</th>
                  <th class="px-6 py-4">Estatus</th>
                  <th class="px-6 py-4">Rol</th>
                  <th class="px-6 py-4">Último acceso</th>
                  <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="u in paginatedUsuarios" :key="u.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium">{{ u.nombre }}</div>
                    <div class="text-xs text-slate-500 dark:text-[#92adc9]">{{ u.correo }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="u.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-200 text-slate-700 dark:bg-border-dark/60 dark:text-[#92adc9]'"
                    >
                      {{ u.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ formatRoles(u.roles) }}</td>
                  <td class="px-6 py-4 text-sm text-slate-500 dark:text-[#92adc9]">{{ formatFecha(u.ultimo_acceso) }}</td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button class="p-1.5 hover:text-primary transition-colors" @click="openEdit(u)">
                        <span class="material-symbols-outlined text-lg">edit</span>
                      </button>
                      <button
                        class="p-1.5 transition-colors"
                        :class="deleteDisabled(u) ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'hover:text-red-500'"
                        :disabled="deleteDisabled(u)"
                        :title="deleteTitle(u)"
                        @click="confirmDelete(u)"
                      >
                        <span class="material-symbols-outlined text-lg">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredUsuarios.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="5">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationBar
            :page="usuariosPage"
            :total-pages="usuariosTotalPages"
            :range-start="usuariosRange.start"
            :range-end="usuariosRange.end"
            :total-items="filteredUsuarios.length"
            @update:page="usuariosPage = $event"
          />
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
            <h4 class="font-bold text-lg">Roles</h4>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
              {{ roles.length }} roles
            </span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-border-dark/30 text-slate-500 dark:text-[#92adc9] text-xs font-bold uppercase tracking-wider">
                <tr>
                  <th class="px-6 py-4">Nombre</th>
                  <th class="px-6 py-4 text-right">Usuarios</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                <tr v-for="r in roles" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-border-dark/20 transition-colors">
                  <td class="px-6 py-4 text-sm font-medium">{{ r.name }}</td>
                  <td class="px-6 py-4 text-right text-sm">{{ r.total }}</td>
                </tr>
                <tr v-if="roles.length === 0">
                  <td class="px-6 py-6 text-center text-sm text-slate-500 dark:text-[#92adc9]" colspan="2">Sin registros</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
    >
      <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark shadow-xl">
        <div class="p-6 border-b border-slate-200 dark:border-border-dark flex items-center justify-between">
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ isEditing ? 'Editar usuario' : 'Nuevo usuario' }}</h4>
            <p class="text-xs text-slate-500 dark:text-[#92adc9]">Campos obligatorios: nombre, correo, estatus y rol.</p>
          </div>
          <button class="p-2 hover:text-red-500 transition-colors" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.nombre ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="fieldErrors.nombre" class="mt-1 text-xs text-red-500">{{ fieldErrors.nombre }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Correo</label>
              <input
                v-model="form.correo"
                type="email"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.correo ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="fieldErrors.correo" class="mt-1 text-xs text-red-500">{{ fieldErrors.correo }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.password ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
                :placeholder="isEditing ? 'Dejar vacío para no cambiar' : 'Mínimo 8 caracteres'"
              />
              <p v-if="fieldErrors.password" class="mt-1 text-xs text-red-500">{{ fieldErrors.password }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Estatus</label>
              <select
                v-model="form.estatus_actual"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.estatus_actual ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              >
                <option value="">Selecciona</option>
                <option value="Temporal">Temporal</option>
                <option value="Base">Base</option>
              </select>
              <p v-if="fieldErrors.estatus_actual" class="mt-1 text-xs text-red-500">{{ fieldErrors.estatus_actual }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">RPE</label>
              <input
                v-model="form.rpe"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.rpe ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="fieldErrors.rpe" class="mt-1 text-xs text-red-500">{{ fieldErrors.rpe }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">RTT</label>
              <input
                v-model="form.rtt"
                type="text"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :class="fieldErrors.rtt ? 'border-red-400 dark:border-red-500' : 'border-slate-200 dark:border-border-dark'"
              />
              <p v-if="fieldErrors.rtt" class="mt-1 text-xs text-red-500">{{ fieldErrors.rtt }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Activo</label>
              <select
                v-model="form.activo"
                class="mt-2 w-full bg-slate-50 dark:bg-border-dark/60 border rounded-lg px-3 py-2 text-sm"
                :disabled="isEditingLastAdmin"
              >
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
              <p v-if="isEditingLastAdmin" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                No puedes desactivar al último administrador activo.
              </p>
            </div>
          </div>

          <div>
            <label class="text-xs uppercase tracking-wider text-slate-500 dark:text-[#92adc9] font-semibold">Roles</label>
            <div class="mt-2 flex flex-wrap gap-2">
              <label
                v-for="role in adminRoles"
                :key="role.id"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-sm cursor-pointer"
                :class="isEditingLastAdmin && role.name === 'administrador' ? 'opacity-60 cursor-not-allowed' : ''"
              >
                <input
                  type="checkbox"
                  class="rounded text-primary focus:ring-primary"
                  :value="role.name"
                  v-model="form.roles"
                  :disabled="isEditingLastAdmin && role.name === 'administrador'"
                />
                <span>{{ role.name }}</span>
              </label>
            </div>
            <p v-if="isEditingLastAdmin" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
              El último administrador debe conservar su rol.
            </p>
            <p v-if="fieldErrors.roles" class="mt-1 text-xs text-red-500">{{ fieldErrors.roles }}</p>
          </div>
        </div>
        <div class="p-6 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2">
          <button class="px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark text-slate-700 dark:text-[#92adc9]" @click="closeModal">
            Cancelar
          </button>
          <button class="px-4 py-2 rounded-lg bg-primary text-white font-bold" @click="saveUser">
            {{ isEditing ? 'Guardar cambios' : 'Crear usuario' }}
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

const usuarios = ref([]);
const roles = ref([]);
const adminRoles = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const currentEditingUser = ref(null);
const userQuery = ref('');
const userStatus = ref('');
const userRole = ref('');
const usuariosPage = ref(1);
const perPage = ref(20);
const fieldErrors = reactive({});
const toast = reactive({
  visible: false,
  message: '',
  type: 'success',
});
const form = reactive({
  nombre: '',
  correo: '',
  password: '',
  activo: 1,
  estatus_actual: '',
  rpe: '',
  rtt: '',
  roles: [],
});

const formatFecha = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleString();
};

const formatRoles = (value) => {
  if (!value || value.length === 0) return '-';
  const names = value.map((role) => role.name ?? role).filter(Boolean);
  return names.join(', ');
};

const loadData = async () => {
  const config = await fetchConfiguracionSistema();
  const map = Object.fromEntries(config.map((item) => [item.clave, item.valor]));
  const per = Number(map['paginacion.default_per_page'] ?? 20);
  perPage.value = Number.isFinite(per) && per > 0 ? per : 20;

  const usuariosRes = await window.axios.get('/api/v1/admin/usuarios');
  usuarios.value = usuariosRes.data?.data ?? usuariosRes.data;

  const rolesRes = await window.axios.get('/api/v1/admin/roles');
  adminRoles.value = rolesRes.data?.data ?? rolesRes.data;

  const counts = new Map();
  usuarios.value.forEach((user) => {
    const userRoles = Array.isArray(user.roles) && user.roles.length > 0 ? user.roles : ['Invitado'];
    userRoles.forEach((role) => {
      const name = role?.name ?? role;
      if (!name) return;
      counts.set(name, (counts.get(name) ?? 0) + 1);
    });
  });

  roles.value = Array.from(counts.entries()).map(([name, total], index) => ({
    id: `${name}-${index}`,
    name,
    total,
  }));
};

const isAdmin = (user) => Array.isArray(user.roles) && user.roles.includes('administrador');

const activeAdminCount = computed(() => usuarios.value.filter((u) => u.activo && isAdmin(u)).length);

const isLastAdmin = (user) => isAdmin(user) && user.activo && activeAdminCount.value <= 1;

const filteredUsuarios = computed(() => {
  const q = userQuery.value.trim().toLowerCase();
  return usuarios.value.filter((u) => {
    const matchQuery =
      !q ||
      String(u.nombre || '').toLowerCase().includes(q) ||
      String(u.correo || '').toLowerCase().includes(q) ||
      String(u.rpe || '').toLowerCase().includes(q) ||
      String(u.rtt || '').toLowerCase().includes(q);
    const matchStatus =
      !userStatus.value ||
      (userStatus.value === 'activo' && u.activo) ||
      (userStatus.value === 'inactivo' && !u.activo);
    const rolesArray = Array.isArray(u.roles) ? u.roles : [];
    const matchRole =
      !userRole.value ||
      (userRole.value === 'Invitado' && rolesArray.length === 0) ||
      rolesArray.includes(userRole.value);
    return matchQuery && matchStatus && matchRole;
  });
});

const usuariosTotalPages = computed(() => Math.max(1, Math.ceil(filteredUsuarios.value.length / perPage.value)));

const paginatedUsuarios = computed(() => {
  const start = (usuariosPage.value - 1) * perPage.value;
  return filteredUsuarios.value.slice(start, start + perPage.value);
});

const usuariosRange = computed(() => {
  if (!filteredUsuarios.value.length) return { start: 0, end: 0 };
  const start = (usuariosPage.value - 1) * perPage.value + 1;
  const end = Math.min(usuariosPage.value * perPage.value, filteredUsuarios.value.length);
  return { start, end };
});

watch([userQuery, userStatus, userRole], () => {
  usuariosPage.value = 1;
});

watch(usuariosTotalPages, (value) => {
  if (usuariosPage.value > value) usuariosPage.value = value;
});

const clearFilters = () => {
  userQuery.value = '';
  userStatus.value = '';
  userRole.value = '';
};

const resetForm = () => {
  form.nombre = '';
  form.correo = '';
  form.password = '';
  form.activo = 1;
  form.estatus_actual = '';
  form.rpe = '';
  form.rtt = '';
  form.roles = [];
  Object.keys(fieldErrors).forEach((key) => {
    fieldErrors[key] = '';
  });
};

const openCreate = () => {
  resetForm();
  isEditing.value = false;
  editingId.value = null;
  currentEditingUser.value = null;
  showModal.value = true;
};

const openEdit = (user) => {
  resetForm();
  isEditing.value = true;
  editingId.value = user.id;
  currentEditingUser.value = user;
  form.nombre = user.nombre || '';
  form.correo = user.correo || '';
  form.activo = user.activo ? 1 : 0;
  form.estatus_actual = user.estatus_actual || '';
  form.rpe = user.rpe || '';
  form.rtt = user.rtt || '';
  form.roles = Array.isArray(user.roles) ? user.roles.map((r) => r.name ?? r) : [];
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  currentEditingUser.value = null;
};

const isEditingLastAdmin = computed(() => {
  const user = currentEditingUser.value;
  if (!user) return false;
  return isAdmin(user) && user.activo && activeAdminCount.value <= 1;
});

const saveUser = async () => {
  Object.keys(fieldErrors).forEach((key) => (fieldErrors[key] = ''));
  try {
    if (isEditing.value) {
      await window.axios.put(`/api/v1/admin/usuarios/${editingId.value}`, form);
    } else {
      await window.axios.post('/api/v1/admin/usuarios', form);
    }
    await loadData();
    showModal.value = false;
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
      fieldErrors[field] = messages?.[0] || 'Dato inválido';
    });
    if (!Object.keys(errors).length) {
      showToast(error.response?.data?.message || 'No se pudo guardar el usuario.', 'error');
    }
  }
};

const confirmDelete = async (user) => {
  if (user.has_historial) {
    showToast('No se puede eliminar porque tiene historial.', 'error');
    return;
  }
  if (isLastAdmin(user)) {
    showToast('No se puede eliminar el último administrador activo.', 'error');
    return;
  }
  if (!confirm(`¿Eliminar usuario ${user.nombre}?`)) return;
  try {
    await window.axios.delete(`/api/v1/admin/usuarios/${user.id}`);
    await loadData();
    showToast('Usuario eliminado correctamente.');
  } catch (error) {
    showToast(error.response?.data?.message || 'No se pudo eliminar el usuario.', 'error');
  }
};

const deleteDisabled = (user) => user.has_historial || isLastAdmin(user);

const deleteTitle = (user) => {
  if (user.has_historial) return 'No se puede eliminar porque tiene historial';
  if (isLastAdmin(user)) return 'No se puede eliminar el último administrador activo';
  return 'Eliminar usuario';
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.visible = true;
  window.setTimeout(() => {
    toast.visible = false;
  }, 2600);
};

onMounted(loadData);
</script>
