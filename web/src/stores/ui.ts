import { defineStore } from 'pinia'

/**
 * Global loading indicator, driven by router navigation guards.
 */
export const useUiStore = defineStore('ui', {
  state: () => ({
    isLoading: false,
  }),
  actions: {
    startLoading(): void {
      this.isLoading = true
    },
    stopLoading(): void {
      this.isLoading = false
    },
  },
})
