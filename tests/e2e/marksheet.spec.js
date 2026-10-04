import { test, expect } from '@playwright/test';

test(
    'Admin can open student marksheets',
    async ({ page }) => {

        test.setTimeout(60000);

        // Open Students
        await page.goto('/students');
        await page.waitForTimeout(1500);

        await expect(
            page.getByRole('heading', { name: 'Students' })
        ).toBeVisible();

        await page.waitForTimeout(1000);

        // Open first student's details
        await page.getByRole(
            'button',
            { name: 'View', exact: true }
        ).first().click();

        await page.waitForTimeout(1500);

        await expect(
            page.getByRole('heading', { name: 'Student Details' })
        ).toBeVisible();

        await page.waitForTimeout(1500);

        // Open student's marksheets
        await page.getByRole(
            'button',
            { name: 'View marksheets', exact: true }
        ).click();

        await page.waitForTimeout(2000);

        await expect(
            page.getByRole('heading', { name: /marksheet/i })
        ).toBeVisible();

        await page.waitForTimeout(1000);

        // Open Create Marksheet
        await page.getByRole(
            'button',
            { name: /create marksheet/i }
        ).first().click();

        await page.waitForTimeout(2000);

        // Verify Create Marksheet form
        await expect(
            page.getByRole('combobox').first()
        ).toBeVisible();

        await expect(
            page.getByPlaceholder('Class will appear here')
        ).toBeVisible();

        await expect(
            page.getByPlaceholder('100').first()
        ).toBeVisible();

        await expect(
            page.getByPlaceholder('40').first()
        ).toBeVisible();

        await expect(
            page.getByPlaceholder('Marks or A').first()
        ).toBeVisible();

        await expect(
            page.getByRole(
                'button',
                { name: 'Save marksheet', exact: true }
            )
        ).toBeVisible();

        await page.waitForTimeout(1500);

        // Select student
        await page.getByRole('combobox').first().selectOption({
            label: 'Test Student 1790675888901'
        });

        await page.waitForTimeout(2000);

        // Verify class was populated
        await expect(
            page.getByPlaceholder('Class will appear here')
        ).toHaveValue('10');

        await page.waitForTimeout(1500);

        // Fill Mathematics
        const mathematicsRow = page.getByRole(
            'row',
            { name: 'Mathematics Remove' }
        );

        await mathematicsRow
            .getByPlaceholder('100')
            .fill('100');

        await page.waitForTimeout(700);

        await mathematicsRow
            .getByPlaceholder('40')
            .fill('40');

        await page.waitForTimeout(700);

        await mathematicsRow
            .getByPlaceholder('Marks or A')
            .fill('90');

        await page.waitForTimeout(1500);

        // Fill Nepali
        const nepaliRow = page.getByRole(
            'row',
            { name: 'Nepali Remove' }
        );

        await nepaliRow
            .getByPlaceholder('100')
            .fill('100');

        await page.waitForTimeout(700);

        await nepaliRow
            .getByPlaceholder('40')
            .fill('40');

        await page.waitForTimeout(700);

        await nepaliRow
            .getByPlaceholder('Marks or A')
            .fill('85');

        await page.waitForTimeout(1500);

        // Fill English
        const englishRow = page.getByRole(
            'row',
            { name: 'English Remove' }
        );

        await englishRow
            .getByPlaceholder('100')
            .fill('100');

        await page.waitForTimeout(700);

        await englishRow
            .getByPlaceholder('40')
            .fill('40');

        await page.waitForTimeout(700);

        await englishRow
            .getByPlaceholder('Marks or A')
            .fill('80');

        await page.waitForTimeout(1500);

        // Save marksheet
        const saveResponse = page.waitForResponse(
            response =>
                response.url().includes('/api/marksheets') &&
                response.request().method() === 'POST'
        );

        await page.getByRole(
            'button',
            { name: 'Save marksheet', exact: true }
        ).click();

        await saveResponse;

        // Temporary inspection after save
        await expect(
        page.locator('text=Marksheet saved successfully.')
        ).toBeVisible();

        await page.waitForTimeout(2000);

        await page.getByRole(
        'button',
        { name: 'Back to marksheets', exact: true }
        ).click();

        await page.waitForTimeout(2000);

        const marksheetRow = page.getByRole(
    'row',
    { name: /Test Student 1790675888901/ }
).first();

await expect(marksheetRow).toBeVisible();

await expect(marksheetRow).toContainText('255');
await expect(marksheetRow).toContainText('85%');
await expect(marksheetRow).toContainText('A+');
await expect(marksheetRow).toContainText('Pass');

await page.waitForTimeout(2000);

await page.waitForTimeout(1500);
// Open saved marksheet
await marksheetRow
    .getByRole('button', { name: 'View', exact: true })
    .click();

await page.waitForTimeout(3000);

    }
);
