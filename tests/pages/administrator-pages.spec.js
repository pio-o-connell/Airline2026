const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/airline');

test('TC-031: administrator login opens route management', async ({ page }) => {
    await loginAs(page, 'admin');

    await expect(page.getByRole('heading', { name: 'Routes' })).toBeVisible();
    await expect(page.locator('#menu').getByRole('link', { name: 'Add route' })).toBeVisible();
    await expect(page.locator('#menu').getByRole('link', { name: 'View manifest' })).toBeVisible();
});

test('TC-032: administrator dashboard requires administrator authentication', async ({ page }) => {
    await page.goto('/controller.php?cmd=showAdminDashboard');

    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-033: administrator can open the route editor', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.getByRole('link', { name: 'Routes' }).click();
    await page.locator('a[href*="cmd=showEditRoute"]').first().click();

    await expect(page.getByRole('heading', { name: /Administrator.*Edit Route/i })).toBeVisible();
});

test('TC-035: administrator can open the flight manifest', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.locator('nav[aria-label="Administrator navigation"]')
        .getByRole('link', { name: 'View manifest' }).click();

    await expect(page.getByRole('heading', { name: /Display flights by date/i })).toBeVisible();
});
