import { expect, test } from '@playwright/test';
import { authenticateAs } from './support/auth';

test('genera las pensiones del mes y actualiza el panel administrativo', async ({
  page,
}) => {
  await authenticateAs(page, 'admin');

  let dashboardRequests = 0;
  await page.route('**/api/admin/dashboard', async (route) => {
    dashboardRequests++;
    await route.fulfill({
      json: {
        stats: {
          pending_admissions: 2,
          active_students: 12,
          representatives: 10,
          teachers: 4,
          courses: 3,
          pending_tuitions: dashboardRequests === 1 ? 5 : 8,
        },
        alerts: [],
      },
    });
  });

  let generationRequested = false;
  await page.route('**/api/tuitions/generate', async (route) => {
    generationRequested = route.request().method() === 'POST';
    await route.fulfill({ json: { created: 3 } });
  });

  await page.goto('/admin');
  await expect(
    page.getByRole('heading', { name: 'Panel de Administración' }),
  ).toBeVisible();

  const dialogMessage = new Promise<string>((resolve) => {
    page.once('dialog', async (dialog) => {
      resolve(dialog.message());
      await dialog.accept();
    });
  });
  await page.getByRole('button', { name: 'Generar pensiones del mes' }).click();

  await expect(dialogMessage).resolves.toContain('3');
  expect(generationRequested).toBe(true);
  await expect.poll(() => dashboardRequests).toBe(2);
});
