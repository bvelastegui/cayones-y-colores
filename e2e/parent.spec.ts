import { expect, test } from '@playwright/test';
import { authenticateAs } from './support/auth';

test('envía una selección de pensiones a PayPhone y procesa el retorno aprobado', async ({
  page,
}) => {
  await authenticateAs(page, 'representative');
  await page.route('**/api/me/tuitions', async (route) => {
    await route.fulfill({
      json: [
        {
          id: 11,
          student_id: 30,
          amount: '80.00',
          remaining_balance: '80.00',
          generation_date: '2026-09-01',
          due_date: '2026-09-30',
          status: 'pending',
          concept: 'monthly',
          student: { first_name: 'Ana', last_name: 'Pérez' },
          payments: [],
        },
      ],
    });
  });

  let paymentPayload: Record<string, unknown> | null = null;
  await page.route('**/api/me/payments/payphone', async (route) => {
    paymentPayload = route.request().postDataJSON() as Record<string, unknown>;
    await route.fulfill({
      json: {
        payment_url: 'http://127.0.0.1:4173/parent/payments?payment=approved',
      },
    });
  });

  await page.goto('/parent/payments');
  await page.locator('#tuition-11').check();
  await page
    .getByRole('button', { name: 'Pagar selección con PayPhone' })
    .click();

  await expect(page.getByText('Pago confirmado correctamente')).toBeVisible();
  expect(paymentPayload).toEqual({ tuition_ids: [11] });
});
