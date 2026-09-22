import { computed, onUnmounted, ref, watch } from 'vue'

/**
 * UX-only ticking countdown to `expiresAt` — the BE re-checks expiry on
 * every request regardless, see docs/shared/business-rules.md#cart-expiry.
 *
 * @param totalSeconds Known full window length, for an accurate
 *   `progressPercent` — deriving it from `expiresAt - now` instead would
 *   read as "full" whenever the component (re)mounts partway through.
 */
export function useCountdown(expiresAt: () => string | null, totalSeconds = 0) {
  const now = ref(Date.now())
  let interval: ReturnType<typeof setInterval> | undefined

  function start(): void {
    clearInterval(interval)
    interval = setInterval(() => {
      now.value = Date.now()
    }, 1000)
  }

  watch(expiresAt, (value) => (value ? start() : clearInterval(interval)), { immediate: true })
  onUnmounted(() => clearInterval(interval))

  const remainingSeconds = computed(() => {
    const target = expiresAt()
    if (!target) {
      return 0
    }

    return Math.max(0, Math.floor((new Date(target).getTime() - now.value) / 1000))
  })

  const progressPercent = computed(() =>
    totalSeconds > 0 ? (remainingSeconds.value / totalSeconds) * 100 : 0,
  )

  const isExpired = computed(() => expiresAt() !== null && remainingSeconds.value === 0)

  const formatted = computed(() => {
    const minutes = Math.floor(remainingSeconds.value / 60)
    const seconds = remainingSeconds.value % 60

    return `${minutes}:${seconds.toString().padStart(2, '0')}`
  })

  return { remainingSeconds, isExpired, formatted, progressPercent }
}
