const { expect } = require('@playwright/test');

const CUSTOMER_USERNAME = process.env.PW_AIRLINE_USERNAME || 'aoifebyrne';
const CUSTOMER_PASSWORD = process.env.PW_AIRLINE_PASSWORD || 'aoife2026';
const SECOND_CUSTOMER_USERNAME = process.env.PW_AIRLINE_SECOND_USERNAME || 'cianmurphy';
const SECOND_CUSTOMER_PASSWORD = process.env.PW_AIRLINE_SECOND_PASSWORD || 'cian2026';
const SUSPENDED_USERNAME = process.env.PW_AIRLINE_SUSPENDED_USERNAME || 'eoinnolan';
const SUSPENDED_PASSWORD = process.env.PW_AIRLINE_SUSPENDED_PASSWORD || 'eoin2026';
const ADMIN_USERNAME = process.env.PW_AIRLINE_ADMIN_USERNAME || 'rmiller';
const ADMIN_PASSWORD = process.env.PW_AIRLINE_ADMIN_PASSWORD || 'rmiller';

async function loginAs(page, role) {
    const credentials = {
        admin: {
            username: ADMIN_USERNAME,
            password: ADMIN_PASSWORD
        },
        customer: {
            username: CUSTOMER_USERNAME,
            password: CUSTOMER_PASSWORD
        },
        secondCustomer: {
            username: SECOND_CUSTOMER_USERNAME,
            password: SECOND_CUSTOMER_PASSWORD
        },
        suspendedCustomer: {
            username: SUSPENDED_USERNAME,
            password: SUSPENDED_PASSWORD
        }
    }[role];

    if (!credentials) {
        throw new Error(`Unknown Airline2026 account role: ${role}`);
    }

    await page.goto('/controller.php?cmd=showLogin');
    await page.locator('input[name="username"]').fill(credentials.username);
    await page.locator('input[name="password"]').fill(credentials.password);
    await page.locator('input[type="submit"][value="authenticateUser"]').click();

    await expect(page.locator('#identity')).toContainText(credentials.username);
}

async function searchFlights(page, {
    route = '1',
    date = '2026-10-29',
    passengers = '1'
} = {}) {
    await page.goto('/controller.php');
    await page.locator('#route').selectOption(route);
    await page.locator('#date').fill(date);
    await page.locator('#seatCount').fill(passengers);
    await page.locator('input.find-flights').click();
    await expect(page.locator('.flight-results')).toBeVisible();
}

async function addFirstFlightToCart(page, options) {
    await searchFlights(page, options);
    const targetDate = options && options.date;
    const flightCard = targetDate
        ? page.locator('.flight-card').filter({ hasText: targetDate }).first()
        : page.locator('.flight-card').first();
    await flightCard.getByRole('link', { name: 'Select flight' }).click();
    await expect(page.locator('#details-title')).toBeVisible();
    await page.locator('input[type="submit"][value="Add to Cart"]').click();
    await expect(page.locator('#content')).toContainText(/added|cart|booked/i);
}

module.exports = { addFirstFlightToCart, loginAs, searchFlights };
