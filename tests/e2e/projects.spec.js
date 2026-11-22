import { test, expect } from '@playwright/test';

test('projects index, filter, detail page, theme toggle, and mobile layout work cleanly', async ({ page }) => {
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
    await page.goto('/proyek');

    await expect(page).toHaveTitle(/Proyek & Aplikasi/);
    await expect(page.getByRole('heading', { level: 1, name: /Proyek & Aplikasi/i })).toBeVisible();
    await expect(page.getByRole('heading', { level: 2, name: 'awork.id' })).toBeVisible();
    await expect(page.getByRole('heading', { level: 2, name: 'ODM Studio App' })).toBeVisible();

    const desktopOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(desktopOverflow).toBe(false);

    // Filter to Operasi Drone
    await page.getByRole('navigation', { name: /Filter kategori proyek/i }).getByRole('link', { name: 'Operasi Drone' }).click();
    await expect(page).toHaveURL(/\/proyek\?type=drone$/);
    await expect(page.getByRole('heading', { level: 2, name: 'ODM Studio App' })).toBeVisible();

    // Filter to Studi Kasus (empty state) and reset
    await page.getByRole('navigation', { name: /Filter kategori proyek/i }).getByRole('link', { name: 'Studi Kasus' }).click();
    await expect(page.getByRole('heading', { level: 2, name: /Belum ada proyek pada kategori ini/i })).toBeVisible();
    await page.getByRole('link', { name: 'Tampilkan semua proyek' }).click();
    await expect(page).toHaveURL(/\/proyek$/);

    // Navigate to project detail
    await page.getByRole('link', { name: 'awork.id' }).first().click();
    await expect(page).toHaveURL(/\/proyek\/awork-id$/);
    await expect(page.getByRole('heading', { level: 1, name: 'awork.id' })).toBeVisible();

    // Theme toggle light -> dark
    const themeToggle = page.locator('#theme-toggle');
    await themeToggle.click();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
    await themeToggle.click();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

    // Mobile layout check (375px) on detail and index
    await page.setViewportSize({ width: 375, height: 812 });
    const mobileDetailOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(mobileDetailOverflow).toBe(false);

    await page.goto('/proyek');
    const mobileIndexOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(mobileIndexOverflow).toBe(false);

    expect(consoleErrors).toEqual([]);
});
