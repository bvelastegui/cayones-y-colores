import { createReadStream, readFileSync, statSync } from 'node:fs';
import { createServer } from 'node:http';
import { extname, join, normalize, resolve } from 'node:path';

const publicDirectory = resolve('public');
const manifest = JSON.parse(
  readFileSync(join(publicDirectory, 'build/manifest.json'), 'utf8'),
);
const entry = manifest['resources/js/app.ts'];
const stylesheet = manifest['resources/css/app.css'];

if (!entry) {
  throw new Error(
    'No se encontró la entrada principal en el manifest de Vite.',
  );
}

if (!stylesheet) {
  throw new Error(
    'No se encontró la hoja de estilos principal en el manifest de Vite.',
  );
}

const mimeTypes = {
  '.css': 'text/css',
  '.ico': 'image/x-icon',
  '.js': 'text/javascript',
  '.json': 'application/json',
  '.mjs': 'text/javascript',
  '.svg': 'image/svg+xml',
  '.webmanifest': 'application/manifest+json',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
};

const styles = [...new Set([stylesheet.file, ...(entry.css ?? [])])]
  .map((file) => `<link rel="stylesheet" href="/build/${file}">`)
  .join('');
const indexHtml = `<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crayones y Colores</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="manifest" href="/build/manifest.webmanifest">
    <meta name="theme-color" content="#f97316">
    ${styles}
    <script type="module" src="/build/${entry.file}"></script>
  </head>
  <body class="font-sans antialiased">
    <div id="app"></div>
  </body>
</html>`;

createServer((request, response) => {
  const pathname = decodeURIComponent(
    new URL(request.url ?? '/', 'http://localhost').pathname,
  );

  if (pathname.startsWith('/api/')) {
    response.writeHead(500, { 'Content-Type': 'application/json' });
    response.end(
      JSON.stringify({ message: `API E2E no simulada: ${pathname}` }),
    );

    return;
  }

  const relativePath = normalize(pathname).replace(/^\/+/, '');
  const filePath = resolve(publicDirectory, relativePath);

  if (filePath.startsWith(publicDirectory)) {
    try {
      if (statSync(filePath).isFile()) {
        const headers = {
          'Content-Type':
            mimeTypes[extname(filePath)] ?? 'application/octet-stream',
        };

        if (pathname === '/build/sw.js') {
          headers['Service-Worker-Allowed'] = '/';
        }

        response.writeHead(200, headers);
        createReadStream(filePath).pipe(response);

        return;
      }
    } catch {
      // Las rutas del cliente se resuelven con el documento principal.
    }
  }

  response.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  response.end(indexHtml);
}).listen(4173, '127.0.0.1');
