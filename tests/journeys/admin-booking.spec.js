const { test, expect } = require('@playwright/test');
const { loginAs, addFirstFlightToCart, searchFlights } = require('../helpers/airline');

// rmiller lands on AdminView at the bare URL.
// To book, they click "Public home" in the admin navigation,
// which loads the public flight-search workspace.
// searchFlights and addFlightToCart route through CustomerFlightResultsView
// because username !== "Guest".
// Cart state is NOT saved on logout for rmiller (explicitly excluded in
// the logout handler and the booking_owner logic).

test('TC-060: administrator adds a flight to the Cart @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');

    // Navigate from admin dashboard to public homepage
    await page.locator('#menu').getByRole('link', { name: 'Public home' }).click();
    await expect(page.locator('.flight-workspace')).toBeVisible();

    // Search and add flight
    await searchFlights(page, { route: '1', date: '2026-10-29', passengers: '1' });
    await page.locator('.flight-card').first().getByRole('link', { name: 'Select flight' }).click();
    await expect(page.locator('#details-title')).toBeVisible();
    await page.locator('input[type="submit"][value="Add to Cart"]').click();

    // Verify Cart
    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
    await expect(page.locator('.cart-flight-card')).toContainText('PA');

    // Admin identity must be unchanged throughout
    await expect(page.locator('#identity')).toContainText('rmiller');
});

test('TC-061: administrator selects seats @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });

    await page.goto('/controller.php?cmd=showSeatSelection');
    await expect(page.locator('.aircraft-seat-map')).toBeVisible();
    await expect(page.locator('.seat-map-panel h2')).toContainText(/AC(180|220)/);

    await page.locator('.seat-option input:not(:disabled)').first().check();
    await expect(page.locator('.seat-save-button')).toBeEnabled();
    await page.locator('.seat-save-button').click();

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.locator('#identity')).toContainText('rmiller');
});

test('TC-062: administrator reaches the Stripe payment button @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });

    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await page.getByLabel('Full name').fill('Admin Passenger');
    await page.getByLabel('Email address').fill('admin.passenger@example.test');
    await page.getByLabel('Passport number').fill('AP123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.getByRole('button', { name: 'Pay securely with Stripe' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Pay securely with Stripe' })).toBeEnabled();
    await expect(page.locator('#identity')).toContainText('rmiller');
});

test('TC-063: administrator Cart is not saved on logout @FUN @REG', {
    tag: ['@FUN', '@REG']
}, async ({ page }) => {
    // Add a flight to the admin Cart
    await loginAs(page, 'admin');
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29', passengers: '1' });
    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);

    // Log out — rmiller Cart state must NOT be saved to customer_booking_state
    await page.goto('/controller.php?cmd=logout');
    await expect(page.locator('#identity')).toContainText('Guest');

    // Log back in — Cart must be empty (not restored)
    await loginAs(page, 'admin');
    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('#content')).toContainText(/currently empty/i);
});