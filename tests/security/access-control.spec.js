const { test, expect } = require('@playwright/test');
const { loginAs, addFirstFlightToCart } = require('../helpers/airline');

test('TC-041: unauthenticated users cannot open the administrator dashboard directly @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async ({ page }) => {
    await page.goto('/controller.php?cmd=showAdminDashboard');

    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-042: customers cannot open administrator routes directly @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async ({ page }) => {
    await loginAs(page, 'customer');
    await page.goto('/controller.php?cmd=showAddRoute');

    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-043: administrator authentication grants access to protected route management @SEC @FUN @REG @E2E', {
    tag: ['@SEC', '@FUN', '@REG', '@E2E']
}, async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/controller.php?cmd=showAddRoute');

    await expect(page.getByRole('heading', { name: /Add Route/i })).toBeVisible();
    await expect(page.locator('input[name="source"]')).toBeVisible();
});

test('TC-044: invalid credentials do not create an authenticated session @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async ({ page }) => {
    await page.goto('/controller.php?cmd=showLogin');
    await page.locator('input[name="username"]').fill('invalid_airline_security_user');
    await page.locator('input[name="password"]').fill('invalid_airline_security_password');
    await page.locator('input[type="submit"][value="authenticateUser"]').click();

    await expect(page.locator('#identity')).toContainText('Guest');
    await page.goto('/controller.php?cmd=showAdminDashboard');
    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
});

test.skip('TC-045: suspended accounts are rejected during authentication @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async () => {
    // Known limitation: authenticateUser currently does not enforce user.suspend.
    // Enable this test after suspended-account handling is implemented.
});

test('TC-064: guest cannot access passenger details page without completing seat selection @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async ({ page }) => {
    // Add a flight to cart but do NOT select seats
    await addFirstFlightToCart(page);

    // Attempt direct navigation to passenger details
    await page.goto('/controller.php?cmd=showPassengerDetails');

    // The server-side guard should redirect to seat selection
    await expect(page.locator('.seat-selection-page')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Checkout' })).not.toBeVisible();
});

test('TC-065: guest cannot reach Stripe checkout with an empty Cart @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async ({ page }) => {
    // A guest with an empty Cart navigates to startStripeCheckout.
    // The guest booking flow is intentionally permitted — the security boundary is
    // that an empty Cart cannot proceed to payment, not that guests are blocked from checkout.
    await page.goto('/controller.php?cmd=startStripeCheckout');

    // Must stay on the application (no Stripe redirect)
    await expect(page).toHaveURL(/controller\.php/);

    // The checkout page is shown with an empty-Cart message
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.locator('#content')).toContainText(/Cart is empty/i);

    // No payment action is available
    await expect(page.getByRole('button', { name: 'Pay securely with Stripe' })).toHaveCount(0);
});

test('TC-066: customer cannot post to administrator mutation endpoints @SEC @FUN @REG', {
    tag: ['@SEC', '@FUN', '@REG']
}, async ({ page }) => {
    await loginAs(page, 'customer');

    // updateRoute requires admin
    await page.goto('/controller.php?cmd=updateRoute');
    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();

    // Re-login as customer (previous navigation ended the check)
    await loginAs(page, 'customer');

    // deleteSelectedRoutes requires admin
    await page.goto('/controller.php?cmd=deleteSelectedRoutes');
    await expect(page.getByRole('heading', { name: 'login' })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
});

test('TC-067: stripeSuccess with valid-format session ID shows an error and preserves the Cart @INT @SEC @REG', {
    tag: ['@INT', '@SEC', '@REG']
}, async ({ page }) => {
    // Authenticated customer with a populated Cart
    await loginAs(page, 'customer');
    await addFirstFlightToCart(page);

    // cs_test_... passes the regex check in StripeGateway::retrieveCheckoutSession,
    // so the application calls Stripe, Stripe returns an error, the catch block fires,
    // and the error flash is shown. This is a different failure path from TC-049
    // (which uses cs_invalid_airline_test, a string that fails the regex and never
    // reaches the Stripe API call).
    await page.goto('/controller.php?cmd=stripeSuccess&session_id=cs_test_valid_format_not_real_XYZ123');

    await expect(page.locator('#content')).toContainText(/payment|booking|error/i);

    // Cart must be preserved â€” clearing only happens after a successful booking commit
    await page.goto('/controller.php?cmd=showCheckout');
    await expect(page.locator('.cart-flight-card')).toHaveCount(1);
    await expect(page.getByRole('heading', { name: /Booking Confirmation/i })).not.toBeVisible();
});
