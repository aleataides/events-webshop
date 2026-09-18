import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify, type ThemeDefinition } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

/**
 * Monochrome Precision theme — see docs/design-system.md.
 */
const monochromeTheme: ThemeDefinition = {
  dark: false,
  colors: {
    background: '#FFFFFF',
    surface: '#FFFFFF',
    'surface-variant': '#F5F5F5',
    'surface-container-low': '#FBF9F9',
    'surface-container-high': '#EAEAEA',
    primary: '#0A0A0A',
    'primary-darken-1': '#000000',
    secondary: '#1A1A1A',
    'secondary-darken-1': '#111111',
    error: '#B00020',
    info: '#737373',
    success: '#111111',
    warning: '#111111',
    'on-background': '#0A0A0A',
    'on-surface': '#0A0A0A',
    'on-surface-variant': '#737373',
    'on-primary': '#FFFFFF',
    'on-secondary': '#FFFFFF',
    border: '#E5E5E5',
    'border-subtle': '#EEEEEE',
  },
}

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'monochromeTheme',
    themes: {
      monochromeTheme,
    },
  },
  defaults: {
    global: {
      ripple: false,
    },
    VCard: {
      elevation: 0,
      rounded: 'sm',
      border: 'sm',
    },
    VBtn: {
      elevation: 0,
      rounded: 'sm',
      height: 44,
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'sm',
      hideDetails: 'auto',
    },
  },
})
