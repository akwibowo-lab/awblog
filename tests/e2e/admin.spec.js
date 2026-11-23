import { test, expect } from '@playwright/test';

test('admin panel redirects unauthenticated user to login and renders cleanly on desktop and mobile', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            consoleErrors.push(msg.text());
        }
    });
    page.on('pageerror', (err) => {
        consoleErrors.push(err.message);
    });

    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/admin');

    await expect(page).toHaveURL(/\/admin\/login/);
    await expect(page.locator('img[src*="aw-orbit-dark.svg"]')).toBeVisible();
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('input[type="password"]')).toBeVisible();

    const desktopOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(desktopOverflow).toBe(false);

    await page.setViewportSize({ width: 375, height: 812 });
    const mobileOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(mobileOverflow).toBe(false);

    expect(consoleErrors).toEqual([]);

    const passwordResetResponse = await page.goto('/admin/password-reset/request');
    expect(passwordResetResponse?.status()).toBe(404);
});
