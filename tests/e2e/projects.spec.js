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

    const firstProjectLink = page.locator('article h2 a').first();
    await expect(firstProjectLink).toBeVisible();
    const firstProjectTitle = (await firstProjectLink.textContent()).trim();

    const desktopOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(desktopOverflow).toBe(false);

    // Filter to Operasi Drone
    await page.getByRole('navigation', { name: /Filter kategori proyek/i }).getByRole('link', { name: 'Operasi Drone' }).click();
    await expect(page).toHaveURL(/\/proyek\?type=drone$/);

    // Filter to Studi Kasus and reset back to all projects
    await page.getByRole('navigation', { name: /Filter kategori proyek/i }).getByRole('link', { name: 'Studi Kasus' }).click();
    await expect(page).toHaveURL(/\/proyek\?type=case_study$/);
    await page.getByRole('navigation', { name: /Filter kategori proyek/i }).getByRole('link', { name: 'Semua' }).click();
    await expect(page).toHaveURL(/\/proyek$/);

    // Navigate to first project detail
    await page.locator('article h2 a').first().click();
    await expect(page).toHaveURL(/\/proyek\/.+/);
    await expect(page.getByRole('heading', { level: 1, name: firstProjectTitle })).toBeVisible();

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
