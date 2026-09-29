import { test, expect } from '@playwright/test';

test('Admin can search for a student', async ({ page }) => {

    await page.goto('/students');

    await expect(
        page.getByRole('heading', { name: 'Students' })
    ).toBeVisible();

    const searchInput = page.getByPlaceholder(
        'Search by name, email or phone'
    );

    await expect(searchInput).toBeVisible();

    await searchInput.fill('Ram Sharma');

    await expect(
        page.getByRole(
            'cell',
            { name: 'Ram Sharma', exact: true }
        )
    ).toBeVisible();

});
