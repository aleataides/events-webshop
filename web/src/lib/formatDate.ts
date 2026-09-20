export function formatEventDateTime(iso: string, separator = ' '): string {
  const date = new Date(iso)
  const pad = (n: number) => n.toString().padStart(2, '0')

  return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}${separator}${pad(date.getHours())}:${pad(date.getMinutes())}`
}
