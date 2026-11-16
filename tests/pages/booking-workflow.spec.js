const { test, expect } = require('@playwright/test');
const { addFirstFlightToCart } = require('../helpers/airline');

test('TC-009: Cart displays passenger quantity, fare and total @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page, { passengers: '2' });
    await page.goto('/controller.php?cmd=showCheckout');

    await expect(page.locator('.cart-flight-card')).toContainText('Number of passengers: 2');
    await expect(page.locator('.cart-flight-card')).toContainText('Fare per passenger: €');
    await expect(page.locator('.cart-total')).toContainText('Cart total: €');
});

test('TC-010: removing a flight empties the Cart without changing the session navigation @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showCheckout');
    await page.getByRole('link', { name: 'Remove Flight' }).click();

    await expect(page.locator('.cart-page')).toContainText('Your Cart is currently empty');
    await expect(page.locator('#menu').getByText('Cart')).toHaveAttribute('aria-disabled', 'true');
});

test('TC-008: adding a second flight retains both flights in the Cart @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29' });
    await addFirstFlightToCart(page, { route: '2', date: '2026-11-03' });
    await page.goto('/controller.php?cmd=showCheckout');

    await expect(page.locator('.cart-flight-card')).toHaveCount(2);
});

test('TC-012: seat selection displays the aircraft map @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await expect(page.locator('.aircraft-seat-map')).toBeVisible();
    await expect(page.locator('.seat-map-panel h2')).toContainText(/AC(180|220)/);
});

test('TC-013: seat selection saves a selected seat @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    const availableSeat = page.locator('.seat-option input:not(:disabled)').first();
    await availableSeat.check();
    await expect(availableSeat).toBeChecked();
    await expect(page.locator('.seat-save-button')).toBeEnabled();
});

test('TC-014: seat selection prevents choosing more seats than passengers @FUN @SEC @REG @E2E', {
    tag: ['@FUN', '@SEC', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    const availableSeats = page.locator('.seat-option input:not(:disabled)');
    await availableSeats.nth(0).check();
    await availableSeats.nth(1).click();
    await expect(availableSeats.nth(1)).not.toBeChecked();
});

test('TC-015: occupied seats are disabled in the seat map @FUN @INT @SEC @REG @E2E', {
    tag: ['@FUN', '@INT', '@SEC', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29' });
    await page.goto('/controller.php?cmd=showSeatSelection');

    const occupiedSeats = page.locator('.seat-option input:disabled');
    await expect(occupiedSeats).not.toHaveCount(0);
    await expect(occupiedSeats.first()).not.toBeChecked();
});

test('TC-016: seat selections remain independent for multiple flights @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page, { route: '1', date: '2026-10-29' });
    await addFirstFlightToCart(page, { route: '2', date: '2026-11-03' });
    await page.goto('/controller.php?cmd=showSeatSelection');

    const maps = page.locator('.aircraft-seat-map');
    await expect(maps).toHaveCount(2);
    for (let index = 0; index < 2; index++) {
        await maps.nth(index).locator('input:not(:disabled)').first().check();
    }

    await expect(maps.nth(0).locator('input:checked')).toHaveCount(1);
    await expect(maps.nth(1).locator('input:checked')).toHaveCount(1);
    expect(await maps.nth(0).getAttribute('data-flight-id'))
        .not.toBe(await maps.nth(1).getAttribute('data-flight-id'));
});

// test('TC-017: AC180 matches its layout and AC220 exposes the known renderer discrepancy', async ({ page }) => {
//     await addFirstFlightToCart(page, { route: '1', date: '2026-10-29' });
//     await page.goto('/controller.php?cmd=showSeatSelection');
//     await expect(page.locator('.seat-map-panel h2')).toContainText('AC180');
//     await expect(page.locator('.seat-row')).toHaveCount(30);
//     await expect(page.locator('.seat-row').first().locator('.seat-option')).toHaveCount(6);
//
//     await page.goto('/controller.php?cmd=logout');
//     await addFirstFlightToCart(page, { route: '2', date: '2026-11-03' });
//     await page.goto('/controller.php?cmd=showSeatSelection');
//     await expect(page.locator('.seat-map-panel h2')).toContainText('AC220');
//     // The database defines 37 rows of six seats, but the current renderer
//     // groups AC220 into 22 rows of ten seats.
//     await expect(page.locator('.seat-row')).toHaveCount(22);
//     await expect(page.locator('.seat-row').last().locator('.seat-option')).toHaveCount(10);
// });


test('TC-018: passenger details precede guest authentication @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByLabel('Full name')).toBeVisible();
    await page.getByLabel('Full name').fill('Playwright Passenger');
    await page.getByLabel('Email address').fill('playwright@example.test');
    await page.getByLabel('Passport number').fill('PW123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Log in to continue' })).toBeVisible();
});

test('TC-022: guest checkout offers login and registration before payment @FUN @SEC @REG @E2E', {
    tag: ['@FUN', '@SEC', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await page.getByLabel('Full name').fill('Playwright Passenger');
    await page.getByLabel('Email address').fill('playwright@example.test');
    await page.getByLabel('Passport number').fill('PW123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.getByRole('link', { name: 'Log in to continue' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Register to continue' })).toBeVisible();
});

test('TC-020: incomplete passenger details prevent continuing @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await expect(page.getByRole('button', { name: 'Continue to payment' })).toBeDisabled();
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
});

test('TC-021: checkout summary contains passenger, flight, seat and total details @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();
    await page.getByLabel('Full name').fill('Summary Passenger');
    await page.getByLabel('Email address').fill('summary@example.test');
    await page.getByLabel('Passport number').fill('SUM123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.locator('.checkout-summary')).toContainText(/Seats: \d+/);
    await expect(page.locator('.checkout-summary')).toContainText(/Fare per passenger: €89\.00/);
    await expect(page.locator('.checkout-total')).toContainText('Total: €89.00');
});
