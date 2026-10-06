import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import Symfony from '@symfony/reprise/vite'

export default defineConfig({
  input: {
    app: './assets/app.js',
  },
  plugins: [
    tailwindcss(),
    Symfony({
      stimulus: './assets/controllers.json',
    }),
  ],
})
