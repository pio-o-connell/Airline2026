const { test, expect } = require('@playwright/test');
const { addFirstFlightToCart } = require('../helpers/airline');

test.use({ viewport: { width: 390, height: 844 } });

test('TC-036: homepage remains usable at 390px', async ({ page }) => {
    await page.goto('/controller.php');

    await expect(page.getByRole('heading', { name: 'PHP Airlines.com' })).toBeVisible();
    await expect(page.locator('#search-title')).toBeVisible();

    const documentWidth = await page.evaluate(() => document.documentElement.scrollWidth);
    const viewportWidth = await page.evaluate(() => window.innerWidth);
    expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 2);
});

test('TC-037: booking and seat selection remain usable at 390px', async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await expect(page.locator('.seat-selection-page')).toBeVisible();
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
});

test('TC-038: booking controls remain usable at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 700 });
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=showSeatSelection');

    await expect(page.locator('.seat-selection-page')).toBeVisible();
    await page.locator('.seat-option input:not(:disabled)').first().check();
    await page.locator('.seat-save-button').click();
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
});
