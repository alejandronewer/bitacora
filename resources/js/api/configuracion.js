export async function fetchConfiguracionSistema() {
  const response = await window.axios.get('/api/v1/configuracion/sistema');
  return response.data?.data ?? response.data;
}

export async function fetchConfiguracionUsuario() {
  const response = await window.axios.get('/api/v1/configuracion/usuario');
  return response.data?.data ?? response.data;
}
