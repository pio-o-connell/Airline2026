const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/airline');

test('TC-023: valid customer credentials authenticate successfully', async ({ page }) => {
    await loginAs(page, 'customer');
    await expect(page.locator('#menu').getByRole('link', { name: 'Logout' })).toBeVisible();
});

test('TC-024: invalid credentials are rejected', async ({ page }) => {
    await page.goto('/controller.php?cmd=showLogin');
    await page.locator('input[name="username"]').fill('invalid_airline_test_user');
    await page.locator('input[name="password"]').fill('invalid_password');
    await page.locator('input[type="submit"][value="authenticateUser"]').click();

    await expect(page.locator('#identity')).toContainText('Guest');
    await expect(page.getByRole('heading', { name: 'Search flights' })).toBeVisible();
});

test('registration page exposes the required account fields', async ({ page }) => {
    await page.goto('/controller.php?cmd=showRegistration');

    await expect(page.getByRole('heading', { name: 'Register' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="PassportNo"]')).toBeVisible();
});
