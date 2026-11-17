import { test, expect } from '@playwright/test';

test('home page renders title without console errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            consoleErrors.push(msg.text());
        }
    });
    page.on('pageerror', (err) => {
        consoleErrors.push(err.message);
    });

    await page.goto('/');

    await expect(page).toHaveTitle(/Akut Wibowo/);
    await expect(page.getByRole('heading', { name: 'Akut Wibowo' })).toBeVisible();
    expect(consoleErrors).toEqual([]);
});

