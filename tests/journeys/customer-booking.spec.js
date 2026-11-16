const { test, expect } = require('@playwright/test');
const { loginAs, addFirstFlightToCart } = require('../helpers/airline');

test('TC-046: registered customer can search, select seats and retain checkout state', async ({ page }) => {
    await loginAs(page, 'secondCustomer');
    await addFirstFlightToCart(page, {
        route: '1',
        date: '2026-10-29',
        passengers: '1'
    });

    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
    await expect(page.locator('.cart-flight-card')).toContainText('PA');

    await page.reload();
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);

    await page.goto('/controller.php?cmd=showSeatSelection');
    const availableSeat = page.locator('.seat-option input:not(:disabled)').first();
    await availableSeat.check();
    await page.locator('.seat-save-button').click();

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await page.getByLabel('Full name').fill('Customer Journey');
    await page.getByLabel('Email address').fill('customer.journey@example.test');
    await page.getByLabel('Passport number').fill('CJ123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.locator('.checkout-summary')).toContainText(/Seats: \d+/);
    await expect(page.locator('.checkout-total')).toContainText('Total: €89.00');

    await page.reload();
    await expect(page.getByLabel('Full name'))
        .toHaveValue('Customer Journey');
    await expect(page.getByLabel('Email address'))
        .toHaveValue('customer.journey@example.test');
    await expect(page.locator('.checkout-total')).toContainText('Total: €89.00');
});

test('TC-047: customer cannot continue to payment with incomplete passenger details', async ({ page }) => {
    await loginAs(page, 'secondCustomer');
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Continue to payment' })).toBeDisabled();
});

test('TC-048: customer logout ends the authenticated browser session', async ({ page }) => {
    await loginAs(page, 'customer');
    await page.goto('/controller.php?cmd=logout');

    await expect(page.locator('#identity')).toContainText('Guest');
    await expect(page.locator('#menu').getByRole('link', { name: 'Login / Register' }))
        .toBeVisible();
});
