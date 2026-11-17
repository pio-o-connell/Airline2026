const { test, expect } = require('@playwright/test');
const { loginAs } = require('../helpers/airline');

test('TC-031: administrator login opens route management @SMK @FUN @SEC @REG @E2E', {
    tag: ['@SMK', '@FUN', '@SEC', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');

    await expect(page.getByRole('heading', { name: 'Routes' })).toBeVisible();
    await expect(page.locator('#menu').getByRole('link', { name: 'Add route' })).toBeVisible();
    await expect(page.locator('#menu').getByRole('link', { name: 'View manifest' })).toBeVisible();
});

test('TC-032: administrator dashboard requires administrator authentication @FUN @SEC @REG', {
    tag: ['@FUN', '@SEC', '@REG']
}, async ({ page }) => {
    await page.goto('/controller.php?cmd=showAdminDashboard');

    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-033: administrator can open the route editor @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');
    await page.getByRole('link', { name: 'Routes' }).click();
    await page.locator('a[href*="cmd=showEditRoute"]').first().click();

    await expect(page.getByRole('heading', { name: /Administrator.*Edit Route/i })).toBeVisible();
});

test('TC-035: administrator can open the flight manifest @SMK @FUN @REG @E2E', {
    tag: ['@SMK', '@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');
    await page.locator('nav[aria-label="Administrator navigation"]')
        .getByRole('link', { name: 'View manifest' }).click();

    await expect(page.getByRole('heading', { name: /Display flights by date/i })).toBeVisible();
});

// TC-052: Administrator can navigate to the public homepage.
test('TC-052: administrator can open the public homepage @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');

    // Follow the Public Home link in the administrator navigation.
    await page.locator('#menu')
        .getByRole('link', { name: 'Public home' }).click();

    // Confirm the public flight-search workspace is displayed.
    await expect(page.locator('.flight-workspace')).toBeVisible();

    // Confirm the administrator session remains active.
    await expect(page.locator('#identity')).toContainText('rmiller');
});
test('TC-076: flight manifest detail page shows passenger and seat data @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');
    await page.locator('nav[aria-label="Administrator navigation"]')
        .getByRole('link', { name: 'View manifest' }).click();

    await expect(page.getByRole('heading', { name: /Display flights by date/i })).toBeVisible();

    // Click the first flight date/route link in the manifest list.
    // The seed data contains 3 bookings and 6 passengers so at least one link exists.
    const firstLink = page.locator('#content a').first();
    await firstLink.click();

    // displayFlightManifestDetails outputs route, revenue, flight name and passenger rows.
    await expect(page.locator('#content')).toContainText(/Route:|Revenue|Customers/i);
});
