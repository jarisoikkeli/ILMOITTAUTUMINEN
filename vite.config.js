import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  // Tämä on tärkeä buildia varten!
  base: './',
  server: {
    proxy: {
      '/register.php': 'http://localhost/ilmo',
      '/get_kilpailut.php': 'http://localhost/ilmo',
    }
  }
})
