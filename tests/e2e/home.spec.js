import { test, expect } from '@playwright/test';

test('home page renders portfolio sections without console errors or horizontal overflow', async ({ page }) => {
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
    await page.goto('/');

    await expect(page).toHaveTitle(/Akut Wibowo/);
    await expect(page.getByRole('heading', { level: 1, name: 'Akut Wibowo' })).toBeVisible();
    await expect(page.getByRole('heading', { level: 2, name: /Sertifikasi/i })).toBeVisible();
    await expect(page.getByRole('heading', { level: 2, name: /Galeri Aplikasi/i })).toBeVisible();
    await expect(page.getByRole('heading', { level: 2, name: /Tulisan Terbaru/i })).toBeVisible();
    await expect(page.getByRole('heading', { level: 2, name: /Diskusi Proyek/i })).toBeVisible();

    const desktopOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(desktopOverflow).toBe(false);

    await page.setViewportSize({ width: 375, height: 812 });
    const mobileOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(mobileOverflow).toBe(false);

    await page.evaluate(() => window.scrollTo(0, 600));
    const backToTop = page.getByRole('button', { name: 'Kembali ke atas' });
    await expect(backToTop).toBeVisible();
    await backToTop.click();
    await expect.poll(async () => page.evaluate(() => window.scrollY)).toBeLessThan(100);

    expect(consoleErrors).toEqual([]);
});
