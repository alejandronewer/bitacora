export async function fetchConfiguracionSistema({ skipAuthRedirect = false } = {}) {
  const response = await window.axios.get('/api/v1/configuracion/sistema', { skipAuthRedirect });
  return response.data?.data ?? response.data;
}

export async function fetchConfiguracionUsuario({ skipAuthRedirect = false } = {}) {
  const response = await window.axios.get('/api/v1/configuracion/usuario', { skipAuthRedirect });
  return response.data?.data ?? response.data;
}
