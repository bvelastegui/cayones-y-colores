import { expect, test } from '@playwright/test';
import { authenticateAs } from './support/auth';

test('permite al docente seleccionar un curso y consultar sus estudiantes', async ({
  page,
}) => {
  await authenticateAs(page, 'teacher');
  await page.route('**/api/teacher/courses', async (route) => {
    await route.fulfill({
      json: [
        {
          id: 20,
          level: { name: 'Inicial 2' },
          parallel: 'A',
          active_students_count: 1,
        },
      ],
    });
  });
  await page.route('**/api/teacher/reports?*', async (route) => {
    await route.fulfill({ json: { data: [] } });
  });
  await page.route('**/api/teacher/courses/20/students', async (route) => {
    await route.fulfill({
      json: [
        {
          id: 30,
          first_name: 'Ana',
          last_name: 'Pérez',
          pivot: { status: 'active' },
        },
      ],
    });
  });

  await page.goto('/teacher');
  await expect(
    page.getByRole('heading', { name: 'Módulo Docente' }),
  ).toBeVisible();
  await page.getByText('Inicial 2').click();

  await expect(page.getByText('Ana Pérez')).toBeVisible();
  await expect(
    page.getByRole('button', { name: 'Ficha de cuidado' }),
  ).toBeVisible();
  await expect(page.getByRole('button', { name: 'Informe' })).toBeVisible();
});
