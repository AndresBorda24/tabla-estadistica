import { defineConfig } from 'vite'

export default defineConfig({
  server: {
    cors: true
  },
  build: {
    manifest: true,
    outDir: 'public/src',
    copyPublicDir: false,
    rollupOptions: {
      input: 'src/main.js',
    },
  },
});
