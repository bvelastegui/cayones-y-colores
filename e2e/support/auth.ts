import type { Page } from '@playwright/test';

export type UserRole = 'admin' | 'teacher' | 'representative';

export async function authenticateAs(
  page: Page,
  role: UserRole,
): Promise<void> {
  const user = {
    id: 1,
    name: 'Usuario E2E',
    email: `${role}@example.test`,
    role,
  };

  await page.addInitScript(() => {
    localStorage.setItem('token', 'e2e-token');
  });
  await page.route('**/api/user', async (route) => {
    await route.fulfill({ json: user });
  });
}
