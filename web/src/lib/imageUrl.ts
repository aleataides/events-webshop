/**
 * Runtime-built per docs/shared/infra.md — never hardcoded, DB only stores
 * image_id/copyright. Pattern mirrors task.md's Images API example.
 */
export function buildImageUrl(imageId: string): string {
  return `${import.meta.env.VITE_IMAGES_API_BASE_URL}/de/api/image/${imageId}/shop_cover_v3/webp`
}
