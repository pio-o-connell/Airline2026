const { test, expect } = require('@playwright/test');
const { addFirstFlightToCart } = require('../helpers/airline');

test.use({ viewport: { width: 390, height: 844 } });

test('TC-036: homepage remains usable at 390px @FUN @REG @RSP @E2E', {
    tag: ['@FUN', '@REG', '@RSP', '@E2E']
}, async ({ page }) => {
    await page.goto('/controller.php');

    await expect(page.getByRole('heading', { name: 'PHP Airlines.com' })).toBeVisible();
    await expect(page.locator('#search-title')).toBeVisible();

    const documentWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 2);
});

test('TC-037: booking and seat selection remain usable at 390px @FUN @REG @RSP @E2E', {
    tag: ['@FUN', '@REG', '@RSP', '@E2E']
}, async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await expect(page.locator('.seat-selection-page')).toBeVisible();
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
});

test('TC-038: booking controls remain usable at 320px @FUN @REG @RSP @E2E', {
    tag: ['@FUN', '@REG', '@RSP', '@E2E']
}, async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 700 });
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await expect(page.locator('.seat-selection-page')).toBeVisible();
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
});

test('TC-070: homepage is usable at 768px tablet viewport @FUN @RSP @REG', {
    tag: ['@FUN', '@RSP', '@REG']
}, async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/controller.php');

    await expect(page.getByRole('heading', { name: 'PHP Airlines.com' })).toBeVisible();
    await expect(page.locator('#search-title')).toBeVisible();

    const documentWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 2);
});

test('TC-071: Cart page is usable at 390px @FUN @RSP @REG', {
    tag: ['@FUN', '@RSP', '@REG']
}, async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showCheckout');

    await expect(page.locator('.cart-flight-card')).toBeVisible();
    await expect(page.locator('.cart-total')).toBeVisible();

    const documentWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 2);
});

test('TC-072: Cart page is usable at 320px @FUN @RSP @REG', {
    tag: ['@FUN', '@RSP', '@REG']
}, async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 700 });
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showCheckout');

    await expect(page.locator('.cart-flight-card')).toBeVisible();
    await expect(page.locator('.cart-total')).toBeVisible();

    const documentWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 2);
});

test('TC-073: checkout summary is readable at 320px @FUN @RSP @REG', {
    tag: ['@FUN', '@RSP', '@REG']
}, async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 700 });
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();

    await page.getByLabel('Full name').fill('Responsive Passenger');
    await page.getByLabel('Email address').fill('responsive@example.test');
    await page.getByLabel('Passport number').fill('RP123456');
    await page.getByRole('button', { name: 'Continue to payment' }).click();

    await expect(page.locator('.checkout-summary')).toBeVisible();
    await expect(page.locator('.checkout-total')).toBeVisible();

    const documentWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 2);
});
