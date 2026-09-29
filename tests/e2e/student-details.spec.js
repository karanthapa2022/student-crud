import { test, expect } from '@playwright/test';


test('Admin can open student details', async ({ page }) => {

    await page.goto('/students');

    await expect(
        page.getByRole('heading', { name: 'Students' })
    ).toBeVisible();

    await page.getByRole(
        'button',
        { name: 'View', exact: true }
    ).first().click();

    await expect(
        page.getByRole('heading', { name: 'Student Details' })
    ).toBeVisible();

    await expect(
        page.getByText('Student ID', {exact: true})
    ).toBeVisible();

});
