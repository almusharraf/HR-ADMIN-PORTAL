import { defineConfig } from 'vite';
import { resolve } from 'path';

// Backend pages are plain PHP; this build only produces the two asset
// files they include directly, so filenames must stay fixed (no hashing)
// and land inside backend/public/assets/.
export default defineConfig({
  server: {
    proxy: {
      '/api': 'http://localhost:8080',
    },
  },
  build: {
    outDir: resolve(__dirname, '../backend/public/assets'),
    emptyOutDir: false,
    rollupOptions: {
      input: resolve(__dirname, 'src/main.ts'),
      output: {
        entryFileNames: 'admin.js',
        assetFileNames: (info) => (info.name?.endsWith('.css') ? 'admin.css' : '[name][extname]'),
      },
    },
  },
});
