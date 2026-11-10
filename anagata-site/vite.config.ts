import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
export default defineConfig({ plugins: [react(), tailwindcss()], base: '/anagata-assets/', build: { outDir: '../public/anagata-assets', emptyOutDir: true, rollupOptions: { output: { entryFileNames: 'app.js', assetFileNames: 'app[extname]', chunkFileNames: '[name].js' } } } });
