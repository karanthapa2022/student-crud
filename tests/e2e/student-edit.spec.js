import { test, expect } from '@playwright/test';

test('Admin can open and edit student form', async({ page })=>{

    await page.goto('/students');

    await expect(
        page.getByRole('heading', {name: 'students'})
    ).toBeVisible();

    await page.getByRole(
        'button',
        {name: 'Edit', exact: true}
    ).first().click();

    await expect(
        page.getByRole('heading', {name: 'Edit Student'})
    ).toBeVisible();

    await expect(
        page.getByRole('button', { name: 'Update Student', exact: true})
    ).toBeVisible();

    const nameInput = page.getByRole(
        'textbox',
        {name: "Enter student's full name"}
    );
    await expect(nameInput).toBeVisible();
    await expect(nameInput).not.toHaveValue('');

    const updatedName = `Edited Student ${Date.now()}`;

    await nameInput.fill(updatedName);

    await expect(nameInput).toHaveValue(updatedName);

    const updateResponsePromise = page.waitForResponse(
        response =>
            response.url().includes('/api/students/')&&
            response.request().method() ==='POST'
    );

    await page.getByRole(
        'button',
        {name: 'Update Student',exact: true}
    ).click();

    const updateResponse = await updateResponsePromise;
    expect(updateResponse.status()).toBe(200);

    await expect(
        page.getByRole('heading', {name:'Edit Student'})
    ).not.toBeVisible();

    await expect(
    page.getByRole('cell', {name: updatedName, exact: true})
    ).toBeVisible();
});
