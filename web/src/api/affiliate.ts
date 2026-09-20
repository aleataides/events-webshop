import apiClient from '@/api/client'

export function getAffiliate(affiliateId: string): Promise<{ id: string; name: string }> {
  return apiClient
    .get<{ data: { id: string; name: string } }>(`/api/${affiliateId}`)
    .then((response) => response.data.data)
}
