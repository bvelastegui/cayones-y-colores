import { expect, test } from '@playwright/test';

test('envía una solicitud pública de admisión desde un dispositivo móvil', async ({
  page,
}) => {
  await page.setViewportSize({ width: 375, height: 812 });
  await page.route('**/api/levels', async (route) => {
    await route.fulfill({
      json: {
        data: [{ id: 7, name: 'Inicial 2' }],
      },
    });
  });

  let admissionPayload: Record<string, unknown> | null = null;
  await page.route('**/api/admissions', async (route) => {
    admissionPayload = route.request().postDataJSON() as Record<
      string,
      unknown
    >;
    await route.fulfill({ status: 201, json: { id: 15, status: 'pending' } });
  });

  await page.goto('/apply');
  await page.getByRole('combobox', { name: 'Nivel al que aplica' }).click();
  await page.getByRole('option', { name: 'Inicial 2' }).click();
  await page.locator('#first_name').fill('Ana');
  await page.locator('#last_name').fill('Pérez');
  await page.locator('#birth_date').fill('2022-05-10');
  await page.locator('#representative_names').fill('María Pérez');
  await page.locator('#email').fill('maria@example.test');
  await page.locator('#phone').fill('0999999999');
  await page.getByRole('button', { name: 'Enviar solicitud' }).click();

  await expect(page.getByRole('status')).toContainText('¡Solicitud recibida!');
  expect(admissionPayload).toMatchObject({
    level_id: 7,
    applicant_first_name: 'Ana',
    applicant_last_name: 'Pérez',
    contact_email: 'maria@example.test',
  });
});

test('inicia sesión y dirige al administrador a su panel', async ({ page }) => {
  await page.route('**/api/login', async (route) => {
    await route.fulfill({
      json: {
        token: 'admin-token',
        user: {
          id: 1,
          name: 'Administradora',
          email: 'admin@example.test',
          role: 'admin',
        },
      },
    });
  });
  await page.route('**/api/admin/dashboard', async (route) => {
    await route.fulfill({
      json: {
        stats: {
          pending_admissions: 2,
          active_students: 12,
          representatives: 10,
          teachers: 4,
          courses: 3,
          pending_tuitions: 5,
        },
        alerts: [],
      },
    });
  });

  await page.goto('/login');
  await page.locator('#email').fill('admin@example.test');
  await page.locator('#password').fill('password');
  await page.getByRole('button', { name: 'Ingresar' }).click();

  await expect(page).toHaveURL(/\/admin$/);
  await expect(
    page.getByRole('heading', { name: 'Panel de Administración' }),
  ).toBeVisible();
  await expect(
    page.evaluate(() => localStorage.getItem('token')),
  ).resolves.toBe('admin-token');
});

test('muestra el error recibido cuando las credenciales son inválidas', async ({
  page,
}) => {
  await page.route('**/api/login', async (route) => {
    await route.fulfill({
      status: 422,
      json: {
        message: 'Credenciales incorrectas.',
        errors: { email: ['Credenciales incorrectas.'] },
      },
    });
  });

  await page.goto('/login');
  await page.locator('#email').fill('admin@example.test');
  await page.locator('#password').fill('incorrecta');
  await page.getByRole('button', { name: 'Ingresar' }).click();

  await expect(page.getByText('Credenciales incorrectas.')).toBeVisible();
  await expect(page).toHaveURL(/\/login$/);
});

test('redirige al login cuando se visita una ruta protegida sin sesión', async ({
  page,
}) => {
  await page.goto('/admin');

  await expect(page).toHaveURL(/\/login$/);
  await expect(
    page.getByRole('form', { name: 'Formulario de inicio de sesión' }),
  ).toBeVisible();
});
