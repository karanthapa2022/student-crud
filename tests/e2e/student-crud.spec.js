import {test, expect} from '@playwright/test';

test('Admin can open students',async ({ page })=>{

    await page.goto('/dashboard');

    await expect(page).toHaveURL(/dashboard/);

    await page.getByRole('button', {name: '→ Manage students Add, edit'}).click();
    await expect(page).toHaveURL(/students/);

    await expect (page.getByRole('heading',{name: 'Students'})).toBeVisible();
});

test('Admin can open Add students form',async ({ page })=>{

    await page.goto('/students');

    await expect(page.getByRole('heading',{name:'Students'})).toBeVisible();

    await page.getByRole('button', {name:'Add student'}).click();

    await expect(page.getByRole('textbox',{name: 'Name'})).toBeVisible();
});
