const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/airline');

test('TC-041: unauthenticated users cannot open the administrator dashboard directly', async ({ page }) => {
    await page.goto('/controller.php?cmd=showAdminDashboard');

    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-042: customers cannot open administrator routes directly', async ({ page }) => {
    await loginAs(page, 'customer');
    await page.goto('/controller.php?cmd=showAddRoute');

    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-043: administrator authentication grants access to protected route management', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/controller.php?cmd=showAddRoute');

    await expect(page.getByRole('heading', { name: /Add Route/i })).toBeVisible();
    await expect(page.locator('input[name="source"]')).toBeVisible();
});

test('TC-044: invalid credentials do not create an authenticated session', async ({ page }) => {
    await page.goto('/controller.php?cmd=showLogin');
    await page.locator('input[name="username"]').fill('invalid_airline_security_user');
    await page.locator('input[name="password"]').fill('invalid_airline_security_password');
    await page.locator('input[type="submit"][value="authenticateUser"]').click();

    await expect(page.locator('#identity')).toContainText('Guest');
    await page.goto('/controller.php?cmd=showAdminDashboard');
    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
});

test.skip('TC-045: suspended accounts are rejected during authentication', async () => {
    // Known limitation: authenticateUser currently does not enforce user.suspend.
    // Enable this test after suspended-account handling is implemented.
});
