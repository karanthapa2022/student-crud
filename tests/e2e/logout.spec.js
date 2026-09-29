import { test, expect } from '@playwright/test';

test('Admin can logout', async ({ page }) => {

    await page.goto('/dashboard');

    const signOutButton = page.getByRole(
        'button',
        { name: /sign out/i }
    );

    await expect(signOutButton).toBeVisible();

    await signOutButton.click();

    await expect(page).toHaveURL(/login/);

    // Try to access a protected page after logout
    await page.goto('/students');

    await expect(page).toHaveURL(/login/);

});
