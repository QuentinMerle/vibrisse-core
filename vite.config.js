import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';
import fs from 'fs';

const DEV_FLAG = resolve(process.cwd(), '.vite-dev');

export default defineConfig({
  plugins: [
    tailwindcss(),
    {
      name: 'vibrisse-dev-flag',
      buildStart() {
        // Create flag file when Vite dev server starts
        if (this.meta.watchMode) {
          fs.writeFileSync(DEV_FLAG, '');
        }
      },
      buildEnd() {
        // Remove flag file when production build completes
        if (!this.meta.watchMode && fs.existsSync(DEV_FLAG)) {
          fs.unlinkSync(DEV_FLAG);
        }
      },
      closeBundle() {
        if (!this.meta.watchMode && fs.existsSync(DEV_FLAG)) {
          fs.unlinkSync(DEV_FLAG);
        }
      }
    }
  ],
  build: {
    outDir: 'assets',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: resolve(process.cwd(), 'src/js/main.js'),
        style: resolve(process.cwd(), 'src/css/main.css')
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: 'css/[name].[ext]'
      }
    }
  },
  server: {
    cors: true,
    strictPort: true,
    port: 3000,
    hmr: {
      host: 'localhost'
    }
  }
});
