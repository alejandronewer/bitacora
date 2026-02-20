export async function fetchMe() {
  const response = await window.axios.get('/api/v1/me');
  return response.data?.data ?? response.data;
}
