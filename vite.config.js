import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  publicDir: false,
  plugins: [
    vue()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './assets/js')
    }
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'assets/js/app.js',
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name].[ext]'
      }
    }
  },
  server: {
    origin: 'http://localhost:5173',
    cors: true
  }
});
