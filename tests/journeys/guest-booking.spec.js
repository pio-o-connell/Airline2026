const { test, expect } = require('@playwright/test');
const { addFirstFlightToCart, loginAs } = require('../helpers/airline');

test('TC-054: guest completes the full pre-authentication booking journey @SMK @FUN @INT @REG @E2E', {
    tag: ['@SMK', '@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    // Search, select, add to cart
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });

    // Select a seat
    await page.goto('/controller.php?cmd=showSeatSelection');
    await expect(page.locator('.aircraft-seat-map')).toBeVisible();
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    // Enter passenger details
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await page.getByLabel('Full name').fill('Playwright Guest');
    await page.getByLabel('Email address').fill('pw.guest@example.test');
    await page.getByLabel('Passport number').fill('PG123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    // Auth gate must be shown
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Log in to continue' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Register to continue' })).toBeVisible();
});

test('TC-055: guest Cart and passenger details are retained after login @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    // Complete the guest booking journey up to the auth gate
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });

    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await page.getByLabel('Full name').fill('Guest Transfer');
    await page.getByLabel('Email address').fill('guest.transfer@example.test');
    await page.getByLabel('Passport number').fill('GT123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    // Confirm we are at the auth gate before logging in
    await expect(page.getByRole('link', { name: 'Log in to continue' })).toBeVisible();

    // Log in as maevekelly (thirdCustomer — unused by other tests)
    await loginAs(page, 'thirdCustomer');

    // Navigate to the passenger details page — the guest Cart should have transferred
    await page.goto('/controller.php?cmd=showPassengerDetails');

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByLabel('Full name')).toHaveValue('Guest Transfer');
    await expect(page.getByLabel('Email address')).toHaveValue('guest.transfer@example.test');
    await expect(page.locator('.checkout-total')).toContainText('€89.00');
});