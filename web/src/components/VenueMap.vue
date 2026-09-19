<script setup lang="ts">
import { onMounted, useTemplateRef } from 'vue'

const { latitude, longitude, name } = defineProps<{
  latitude: string
  longitude: string
  name: string
}>()

const container = useTemplateRef('container')

onMounted(async () => {
  const [{ default: L }] = await Promise.all([
    import('leaflet'),
    import('leaflet/dist/leaflet.css'),
  ])

  if (!container.value) {
    return
  }

  const lat = Number(latitude)
  const lng = Number(longitude)

  const map = L.map(container.value).setView([lat, lng], 15)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map)
  L.marker([lat, lng]).addTo(map).bindPopup(name)
})
</script>

<template>
  <div ref="container" style="height: 320px" />
</template>
