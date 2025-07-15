import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import { resolve } from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),                    // global
       '@backoffice': fileURLToPath(new URL('./src/Backoffice', import.meta.url)), // ✅ alias utilisé dans ton import
      '@front': fileURLToPath(new URL('./src/Frontoffice', import.meta.url)),   // frontoffice (si tu en ajoutes)
      '@views': fileURLToPath(new URL('./src/Backoffice/views', import.meta.url)),
      '@components': fileURLToPath(new URL('./src/Backoffice/components', import.meta.url)),
      '@examples': fileURLToPath(new URL('./src/Backoffice/examples', import.meta.url)),
    }
  },
  build: {
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'index.html'), // fichier principal
        backoffice: resolve(__dirname, 'public/backoffice.html') // ➕ ajout ici
      }
    }
  }
})
