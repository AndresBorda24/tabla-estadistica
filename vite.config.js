import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    manifest: true,
    // outDir: 'public/src',
    copyPublicDir: false,
    rollupOptions: {
      input: 'src/main.js',
    },
  },
});
