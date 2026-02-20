import { createRouter, createWebHistory } from 'vue-router';
import LoginView from './views/LoginView.vue';
import DashboardView from './views/DashboardView.vue';
import TimelineView from './views/TimelineView.vue';
import PmMatrixView from './views/PmMatrixView.vue';
import CatalogosTecnicosView from './views/CatalogosTecnicosView.vue';
import SistemasExternosView from './views/SistemasExternosView.vue';
import UsuariosRolesView from './views/UsuariosRolesView.vue';
import ConfiguracionView from './views/ConfiguracionView.vue';
import EntradaCreateView from './views/EntradaCreateView.vue';
import InventarioRedesView from './views/InventarioRedesView.vue';
import ImportacionesReglasView from './views/ImportacionesReglasView.vue';
import UbicacionesEquiposView from './views/UbicacionesEquiposView.vue';
import EntradaShowView from './views/EntradaShowView.vue';
import GestionEntradasView from './views/GestionEntradasView.vue';
import { getAuthUser } from './auth';

const routes = [
  { path: '/', redirect: '/timeline' },
  { path: '/timeline', name: 'timeline', component: TimelineView },
  { path: '/entradas/:id', name: 'entrada-show', component: EntradaShowView },
  { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
  {
    path: '/pm-matriz',
    name: 'pm-matriz',
    component: PmMatrixView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/catalogos-tecnicos',
    name: 'catalogos-tecnicos',
    component: CatalogosTecnicosView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/sistemas-externos',
    name: 'sistemas-externos',
    component: SistemasExternosView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/inventario-redes',
    name: 'inventario-redes',
    component: InventarioRedesView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/importaciones-reglas',
    name: 'importaciones-reglas',
    component: ImportacionesReglasView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/ubicaciones-equipos',
    name: 'ubicaciones-equipos',
    component: UbicacionesEquiposView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/usuarios-roles',
    name: 'usuarios-roles',
    component: UsuariosRolesView,
    meta: { requiresAuth: true, roles: ['administrador'] },
  },
  {
    path: '/configuracion',
    name: 'configuracion',
    component: ConfiguracionView,
    meta: { requiresAuth: true, roles: ['administrador', 'operador'] },
  },
  {
    path: '/entradas/nueva',
    name: 'entrada-nueva',
    component: EntradaCreateView,
    meta: { requiresAuth: true, roles: ['administrador', 'operador'] },
  },
  {
    path: '/entradas/gestion',
    name: 'entradas-gestion',
    component: GestionEntradasView,
    meta: { requiresAuth: true, roles: ['administrador', 'operador'] },
  },
  {
    path: '/entradas/:id/editar',
    name: 'entrada-editar',
    component: EntradaCreateView,
    meta: { requiresAuth: true, roles: ['administrador', 'operador'] },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  if (to.meta?.guest) {
    const user = await getAuthUser();
    if (user) return { name: 'dashboard' };
    return true;
  }

  if (to.meta?.requiresAuth) {
    const user = await getAuthUser();
    if (!user) {
      return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (Array.isArray(to.meta.roles) && to.meta.roles.length > 0) {
      const roles = user.roles || [];
      const roleNames = roles
        .map((role) => (typeof role === 'string' ? role : role?.name))
        .filter(Boolean)
        .map((role) => role.toLowerCase());
      const allowed = to.meta.roles.some((role) => roleNames.includes(role));
      if (!allowed) {
        return { name: 'dashboard' };
      }
    }
  }

  return true;
});

export default router;
