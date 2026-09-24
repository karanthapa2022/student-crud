import {test, expect} from '@playwright/test';
test('Admin can login',async ({ page })=>{
    await page.goto('/login');

    await page.getByRole('textbox', { name: 'Email' }).fill(process.env.PLAYWRIGHT_ADMIN_EMAIL);
    await page.getByRole('textbox', { name: 'Password' }).fill(process.env.PLAYWRIGHT_ADMIN_PASSWORD);

    await page.getByRole('textbox',{name: 'password'}).press('Enter');

    await expect(page).toHaveURL(/dashboard/);

    await page.getByRole('button', {name: '→ Manage students Add, edit'}).click();
    await expect(page).toHaveURL(/students/);

    await expect (page.getByRole('heading',{name: 'Students'})).toBeVisible();
});
