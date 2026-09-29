import { test, expect } from '@playwright/test';

test('Admin cannot submit student with fewer than 3 subjects', async ({ page }) => {

    await page.goto('/students');

    await expect(
        page.getByRole('heading', { name: 'Students' })
    ).toBeVisible();

    // Open Add Student form
    await page.getByRole(
        'button',
        { name: 'Add student' }
    ).click();

    // Fill the required student information
    await page.getByRole(
        'textbox',
        { name: "Enter student's full name" }
    ).fill(`Validation Student ${Date.now()}`);

    await page.getByRole('combobox').nth(1).selectOption('10');

    await page.getByRole(
        'textbox',
        { name: 'Enter symbol number' }
    ).fill(`${Date.now()}`);

    await page.locator('input[type="date"]').fill('2000-01-15');

    await page.getByRole('combobox').nth(2).selectOption('active');

    await page.locator(
        'input[placeholder="student@gmail.com"]'
    ).fill(`validation${Date.now()}@example.com`);

    await page.getByRole(
        'textbox',
        { name: '98XXXXXXXX' }
    ).fill('9863338888');

    // Select only 2 subjects
    await page.getByRole(
        'checkbox',
        { name: 'Mathematics (MATH101)', exact: true }
    ).check();

    await page.getByRole(
        'checkbox',
        { name: 'Nepali (NEP101)', exact: true }
    ).check();

    // Expect browser validation alert
    page.once('dialog', async dialog => {
        expect(dialog.message()).toBe(
            'Assign at least 3 registered subjects.'
        );

        await dialog.accept();
    });

    // Try to submit
    const createResponsePromise = page.waitForResponse(
    response =>
        response.url().includes('/api/students') &&
        response.request().method() === 'POST',
    { timeout: 3000 }
).catch(() => null);

page.once('dialog', dialog => {
    expect(dialog.message()).toBe(
        'Assign at least 3 registered subjects.'
    );
});

await page.getByRole(
    'button',
    { name: 'Add Student', exact: true }
).click();

const createResponse = await createResponsePromise;

expect(createResponse).toBeNull();
});
