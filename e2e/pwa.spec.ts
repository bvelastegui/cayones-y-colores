import { expect, test } from '@playwright/test';

test('expone un manifest instalable con identidad y modo standalone', async ({
  page,
}) => {
  await page.goto('/');

  const manifestHref = await page
    .locator('link[rel="manifest"]')
    .getAttribute('href');
  expect(manifestHref).toBe('/build/manifest.webmanifest');

  const response = await page.request.get(manifestHref!);
  expect(response.ok()).toBe(true);
  await expect(response.json()).resolves.toMatchObject({
    name: 'Crayones y Colores - SGA',
    short_name: 'Crayones y Colores',
    display: 'standalone',
    start_url: '/',
    scope: '/',
    theme_color: '#f97316',
    icons: [
      expect.objectContaining({
        src: '/icon.svg',
        purpose: 'any maskable',
      }),
    ],
  });
});

test('registra el service worker y sirve el módulo principal desde precache sin conexión', async ({
  context,
  page,
}) => {
  await page.goto('/');
  await expect
    .poll(() =>
      page.evaluate(async () => {
        const registration = await navigator.serviceWorker.ready;

        return registration.active?.state;
      }),
    )
    .toBe('activated');

  await page.reload();
  await expect
    .poll(() => page.evaluate(() => !!navigator.serviceWorker.controller))
    .toBe(true);

  const mainModule = await page
    .locator('script[type="module"][src]')
    .first()
    .getAttribute('src');
  expect(mainModule).toBeTruthy();

  await context.setOffline(true);
  try {
    const cachedResponse = await page.evaluate(async (url) => {
      const response = await fetch(url);

      return { ok: response.ok, status: response.status };
    }, mainModule!);

    expect(cachedResponse).toEqual({ ok: true, status: 200 });
  } finally {
    await context.setOffline(false);
  }
});
