import { test, expect } from '@playwright/test';

test('Admin can create a temporary student for deletion', async ({ page }) => {

    const uniqueId = Date.now();

    const studentName = `Delete Test Student ${uniqueId}`;
    const studentEmail = `deletetest${uniqueId}@example.com`;
    const studentSymbol = `${uniqueId}`;

    await page.goto('/students');

    await expect(
        page.getByRole('heading', { name: 'Students' })
    ).toBeVisible();

    await page.getByRole(
        'button',
        { name: 'Add student' }
    ).click();

    // Full Name
    await page.getByRole(
        'textbox',
        { name: "Enter student's full name" }
    ).fill(studentName);

    // Class
    await page.getByRole('combobox').nth(1).selectOption('10');

    // Symbol No
    await page.getByRole(
        'textbox',
        { name: 'Enter symbol number' }
    ).fill(studentSymbol);

    // Date of Birth
    await page.locator('input[type="date"]').fill('2000-01-15');

    // Status
    await page.getByRole('combobox').nth(2).selectOption('active');

    // Email
    await page.locator(
        'input[placeholder="student@gmail.com"]'
    ).fill(studentEmail);

    // Phone
    await page.getByRole(
        'textbox',
        { name: '98XXXXXXXX' }
    ).fill('9863338888');

    // Teacher
    await page.getByRole('combobox')
        .nth(5)
        .selectOption({
            label: 'Hari Shiwakoti (Class 10)'
        });

    // Mathematics
    await page.getByRole(
        'checkbox',
        { name: 'Mathematics (MATH101)', exact: true }
    ).check();

    // Nepali
    await page.getByRole(
        'checkbox',
        { name: 'Nepali (NEP101)', exact: true }
    ).check();

    // English
    await page.getByRole(
        'checkbox',
        { name: 'English (ENG101)', exact: true }
    ).check();

    // Submit student
    const createResponsePromise = page.waitForResponse(
        response =>
            response.url().includes('/api/students') &&
            response.request().method() === 'POST'
    );

    await page.getByRole(
        'button',
        { name: 'Add Student', exact: true }
    ).click();

    const createResponse = await createResponsePromise;

    expect(createResponse.status()).toBe(201);

    // Confirm the temporary student appears in the table
    await expect(
        page.getByRole(
            'cell',
            { name: studentName, exact: true }
        )
    ).toBeVisible();

    // Find the exact row belonging to our temporary student
    const studentRow = page.locator('tr').filter({
        hasText: studentName
    });

    await expect(studentRow).toBeVisible();

    // Close Student Details modal
    await page.getByRole(
        'button',
        { name: 'Close', exact: true }
    ).click();

    // Handle browser confirmation dialog
    page.once('dialog', async dialog => {
        expect(dialog.message()).toContain(
            `Are you sure you want to delete ${studentName}?`
        );

        await dialog.accept();
    });

    // Wait for DELETE API request
    const deleteResponsePromise = page.waitForResponse(
        response =>
            response.url().includes('/api/students/') &&
            response.request().method() === 'DELETE'
    );

    // Delete the exact temporary student
    await studentRow.getByRole(
        'button',
        { name: 'Delete', exact: true }
    ).click();

    const deleteResponse = await deleteResponsePromise;

    expect(deleteResponse.status()).toBe(200);

    // Confirm the student is no longer in the table
    await expect(
        page.getByRole(
            'cell',
            { name: studentName, exact: true }
        )
    ).not.toBeVisible();
});
