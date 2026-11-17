const { test, expect } = require('@playwright/test');
const { addFirstFlightToCart, searchFlights } = require('../helpers/airline');

test('TC-001: public homepage loads with the flight-search workspace @SMK @FUN @REG @E2E', {
    tag: ['@SMK', '@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await page.goto('/controller.php');

    await expect(page).toHaveTitle('PHP Airlines');
    await expect(page.getByRole('heading', { name: 'PHP Airlines.com' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Search flights' })).toBeVisible();
    await expect(page.locator('#route')).toBeVisible();
    await expect(page.locator('#date')).toBeVisible();
    await expect(page.locator('#seatCount')).toHaveAttribute('min', '1');
    await expect(page.locator('#seatCount')).toHaveAttribute('max', '20');
});

test('TC-002: guest navigation exposes the correct booking destinations @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await page.goto('/controller.php');

    await expect(page.locator('#menu').getByRole('link', { name: 'Home' }))
        .toHaveAttribute('href', 'controller.php');
    await expect(page.locator('#menu').getByText('Cart')).toHaveAttribute('aria-disabled', 'true');
    await expect(page.locator('#menu').getByText('Checkout')).toHaveAttribute('aria-disabled', 'true');
    await expect(page.locator('#menu').getByRole('link', { name: 'Login / Register' }))
        .toHaveAttribute('href', 'controller.php?cmd=showLogin');
});

test('TC-003: valid route and date search displays matching flights @SMK @FUN @INT @REG @E2E', {
    tag: ['@SMK', '@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await searchFlights(page);

    await expect(page.locator('.flight-card').first()).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Available flights' })).toBeVisible();
    await expect(page.getByText(/flight.*found for your search/i)).toBeVisible();
});

test('TC-004: passenger count input rejects zero @FUN @REG', {
    tag: ['@FUN', '@REG']
}, async ({ page }) => {
    await page.goto('/controller.php');

    await expect(page.locator('#seatCount')).toHaveAttribute('min', '1');
    await expect(page.locator('#seatCount')).toHaveAttribute('max', '20');

    await page.locator('#seatCount').fill('0');
    await expect(page.locator('#seatCount')).toHaveJSProperty('validity.valid', false);
});

test('TC-005: passenger count input rejects values above twenty @FUN @REG', {
    tag: ['@FUN', '@REG']
}, async ({ page }) => {
    await page.goto('/controller.php');

    await page.locator('#seatCount').fill('21');
    await expect(page.locator('#seatCount')).toHaveJSProperty('validity.valid', false);
});

test('TC-006: selecting a flight displays its details and fare @FUN @REG @E2E', {
    tag: ['@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await searchFlights(page);
    await page.getByRole('link', { name: 'Select flight' }).first().click();

    await expect(page.getByRole('heading', { name: 'Flight details' })).toBeVisible();
    await expect(page.locator('.flight-details-card')).toContainText(/Fare/);
    await expect(page.locator('.flight-details-card')).toContainText(/Add to Cart/);
});

test('TC-007: a selected flight remains in the guest Cart @SMK @FUN @INT @REG @E2E', {
    tag: ['@SMK', '@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showCheckout');

    await expect(page.getByRole('heading', { name: 'Your Shopping Cart' })).toBeVisible();
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
});

test('TC-011: guest Cart state survives a page reload @FUN @INT @REG @E2E', {
    tag: ['@FUN', '@INT', '@REG', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showCheckout');

    await page.reload();
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
});