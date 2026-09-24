import { test as setup, expect } from '@playwright/test';

setup('authenticate as admin', async ({ page }) => {
    await page.goto('/login');

    await page.getByRole('textbox', { name: 'Email' })
        .fill(process.env.PLAYWRIGHT_ADMIN_EMAIL);

    await page.getByRole('textbox', { name: 'Password' })
        .fill(process.env.PLAYWRIGHT_ADMIN_PASSWORD);

    await page.getByRole('textbox', { name: 'Password' })
        .press('Enter');

    await expect(page).toHaveURL(/dashboard/);

    await page.waitForTimeout(3000);

    await page.context().storageState({
        path: 'playwright/.auth/admin.json',
    });
});
