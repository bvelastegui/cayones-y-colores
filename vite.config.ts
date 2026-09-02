import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig, lazyPlugins } from 'vite-plus';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
    },
  },
  plugins: lazyPlugins(() => [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
      fonts: [
        bunny('Instrument Sans', {
          weights: [400, 500, 600],
        }),
      ],
    }),
    tailwindcss(),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    VitePWA({
      registerType: 'autoUpdate',
      strategies: 'injectManifest',
      srcDir: 'resources/js',
      filename: 'sw.ts',
      base: '/',
      scope: '/',
      buildBase: '/build/',
      manifest: {
        name: 'Crayones y Colores - SGA',
        short_name: 'Crayones y Colores',
        description:
          'Sistema de Gestión Académica para el centro infantil Crayones y Colores.',
        theme_color: '#f97316',
        background_color: '#ffffff',
        display: 'standalone',
        scope: '/',
        start_url: '/',
        icons: [
          {
            src: '/icon.svg',
            sizes: 'any',
            type: 'image/svg+xml',
            purpose: 'any maskable',
          },
        ],
      },
      injectManifest: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2}'],
      },
    }),
  ]),
  server: {
    watch: {
      ignored: [
        '**/.agents/**',
        '**/.claude/**',
        '**/.cursor/**',
        '**/.junie/**',
        '**/vendor/**',
      ],
    },
  },
  lint: {
    ignorePatterns: [
      'vendor/**',
      'node_modules/**',
      'public/**',
      'bootstrap/ssr/**',
      'tailwind.config.js',
      'resources/js/actions/**',
      'resources/js/components/ui/*',
      'resources/js/routes/**',
      'resources/js/wayfinder/**',
    ],
    options: {
      denyWarnings: true,
      typeAware: true,
    },
  },
  fmt: {
    printWidth: 80,
    tabWidth: 2,
    useTabs: false,
    singleQuote: true,
    semi: true,
    trailingComma: 'all',
    singleAttributePerLine: true,
    htmlWhitespaceSensitivity: 'css',
    ignorePatterns: [
      '.agents/**',
      '.claude/**',
      '.codex/**',
      '.cursor/**',
      '.github/**',
      '.junie/**',
      'docs/**',
      'AGENTS.md',
      'composer.json',
      'resources/js/components/ui/*',
      'resources/views/mail/*',
    ],
    sortTailwindcss: {
      functions: ['clsx', 'cn', 'cva'],
      entryPoint: 'resources/css/app.css',
    },
  },
});
