const { test, expect } = require('@playwright/test');
const { loginAs, addFirstFlightToCart } = require('../helpers/airline');

test('TC-046: registered customer can search, select seats and retain checkout state @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
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

test('TC-047: customer cannot continue to payment with incomplete passenger details @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'secondCustomer');
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Continue to payment' })).toBeDisabled();
});

test('TC-048: customer logout ends the authenticated browser session @SEC @FUN @REG @E2E', {
    tag: ['@SEC', '@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'customer');
    await page.goto('/controller.php?cmd=logout');

    await expect(page.locator('#identity')).toContainText('Guest');
    await expect(page.locator('#menu').getByRole('link', { name: 'Login / Register' }))
        .toBeVisible();
});

test('TC-057: authenticated customer sees the Stripe payment button after completing checkout @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    // Use seanwalsh (fourthCustomer) — unused by other tests
    await loginAs(page, 'fourthCustomer');
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });

    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await page.getByLabel('Full name').fill('Sean Payment');
    await page.getByLabel('Email address').fill('sean.payment@example.test');
    await page.getByLabel('Passport number').fill('SP123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.getByRole('button', { name: 'Pay securely with Stripe' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Pay securely with Stripe' })).toBeEnabled();
});

test('TC-058: customer booking state persists through logout and login @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    // Use finnryan (fifthCustomer) — unused by other tests
    await loginAs(page, 'fifthCustomer');
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });

    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await page.getByLabel('Full name').fill('Finn Ryan');
    await page.getByLabel('Email address').fill('finn.persist@example.test');
    await page.getByLabel('Passport number').fill('FR123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    // Log out — booking state should be saved
    await page.goto('/controller.php?cmd=logout');
    await expect(page.locator('#identity')).toContainText('Guest');

    // Log back in — booking state should be restored
    await loginAs(page, 'fifthCustomer');
    await page.goto('/controller.php?cmd=showPassengerDetails');

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByLabel('Full name')).toHaveValue('Finn Ryan');
    await expect(page.getByLabel('Email address')).toHaveValue('finn.persist@example.test');
});
