import { test, expect } from '@playwright/test';

test('health endpoint /up responds ok without console errors', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (err) => errors.push(err.message));

    const response = await page.goto('/up');
    expect(response?.status()).toBe(200);
    expect(errors).toEqual([]);
});
