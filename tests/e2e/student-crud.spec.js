import {test, expect } from '@playwright/test';
test('Admin can open students', async ({ page }) => {

    const uniqueId= Date.now();
    const studentName= `Test Student ${uniqueId}`;
    const studentEmail= `teststudent${uniqueId}@example.com`;
    const studentSymbol= `${uniqueId}`;

    await page.goto('/students');

    await expect(
        page.getByRole('heading', { name: 'Students' })
    ).toBeVisible();

    await page.getByRole('button', { name: 'Add student' }).click();

    // Full Name
    await expect(
        page.getByRole('textbox', {
            name: "Enter student's full name"
        })
    ).toBeVisible();

    await page.getByRole(
        'textbox',
        { name: "Enter student's full name" }
    ).fill(studentName);

    await expect(
        page.getByRole('textbox', {
            name: "Enter student's full name"
        })
    ).toHaveValue(studentName);

        // Class
await page.getByRole('combobox').nth(1).selectOption('10');

await expect(
    page.getByRole('combobox').nth(1)
).toHaveValue('10');

await page.waitForTimeout(1000);

//Symbol No
    await page.getByRole(
        'textbox',{name: 'Enter symbol number'}
    ).fill(studentSymbol);

    await expect(
        page.getByRole(
            'textbox',{name: 'Enter symbol number'}
        )
    ).toHaveValue(studentSymbol);

    // Date of Birth
    await expect(
        page.locator('input[type="date"]')
    ).toBeVisible();

    await page.locator('input[type="date"]').fill('2000-01-15');

    await expect(
        page.locator('input[type="date"]')
    ).toHaveValue('2000-01-15');

    //Status
    await page.getByRole('combobox').nth(2).selectOption('active');

    await expect(
        page.getByRole('combobox').nth(2)
    ).toHaveValue('active');

    // Email
    const emailInput = page.locator(
    'input[placeholder="student@gmail.com"]'
);

await expect(emailInput).toBeVisible();

await emailInput.fill(studentEmail);

await expect(emailInput).toHaveValue(
    studentEmail
);

    // Phone
    await expect(
        page.getByRole('textbox', { name: '98XXXXXXXX' })
    ).toBeVisible();

    await page.getByRole(
        'textbox',
        { name: '98XXXXXXXX' }
    ).fill('9863338888');

    await expect(
        page.getByRole('textbox', { name: '98XXXXXXXX' })
    ).toHaveValue('9863338888');

        //Teacher
    await page.getByRole('combobox')
    .nth(5)
    .selectOption({ label: 'Hari Shiwakoti (Class 10)' });
    await expect(
        page.getByRole('combobox').nth(5)
    ).toHaveValue('1');

    //Subjects mathematics
    await page
    .getByRole('checkbox',
        {name: 'Mathematics (MATH101)', exact: true}
    ).check();

    await expect(
        page.getByRole('checkbox',{name: 'Mathematics (MATH101)', exact: true})
    ).toBeChecked();

    // Nepali
await page
    .getByRole('checkbox', { name: 'Nepali (NEP101)', exact: true })
    .check();

await expect(
    page.getByRole('checkbox', { name: 'Nepali (NEP101)', exact: true })
).toBeChecked();

// English
await page
    .getByRole('checkbox', { name: 'English (ENG101)', exact: true })
    .check();

await expect(
    page.getByRole('checkbox', { name: 'English (ENG101)', exact: true })
).toBeChecked();

//Submit
const responsePromise = page.waitForResponse(
    response =>
        response.url().includes('/api/students') &&
        response.request().method() === 'POST'
);

await page.getByRole(
    'button',
    { name: 'Add Student', exact: true }
).click();

const response = await responsePromise;

expect(response.status()).toBe(201);


});
