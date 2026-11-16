const { test, expect } = require('@playwright/test');
const { addFirstFlightToCart, loginAs } = require('../helpers/airline');

test('TC-049: invalid Stripe session cannot create a booking and retains the customer Cart', async ({ page }) => {
    await loginAs(page, 'customer');
    await addFirstFlightToCart(page);

    await page.goto('/controller.php?cmd=stripeSuccess&session_id=cs_invalid_airline_test');

    await expect(page.locator('#content')).toContainText(/payment|booking|Cart/i);
    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
    await expect(page.getByRole('heading', { name: /Booking Confirmation/i })).not.toBeVisible();
});

test('TC-050: cancelling payment preserves the guest booking state', async ({ page }) => {
    await addFirstFlightToCart(page);
    await page.goto('/controller.php?cmd=stripeCancel');

    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
});

test.skip('TC-051: successful Stripe payment persists booking records on a disposable database', async () => {
    // Requires STRIPE_SECRET_KEY, Stripe test credentials and a disposable database.
    // This must not run against the working local database.
});
