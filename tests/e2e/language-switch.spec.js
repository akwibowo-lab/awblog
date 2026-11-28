import { test, expect } from '@playwright/test';

test('language switcher toggles ID and EN on home and projects while hidden on blog', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/');

    const langToggle = page.locator('#lang-toggle');
    await expect(langToggle).toBeVisible();
    await expect(langToggle).toHaveText('EN');
    await expect(page.locator('html')).toHaveAttribute('lang', 'id');

    // Switch to EN on homepage
    await langToggle.click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    await expect(page.locator('#lang-toggle')).toHaveText('ID');
    await expect(page.getByRole('heading', { level: 2, name: /Drone Certification/i })).toBeVisible();

    // Navigate to /proyek and verify EN persists
    await page.goto('/proyek');
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    await expect(page.locator('#lang-toggle')).toBeVisible();
    await expect(page.getByRole('heading', { level: 1, name: /Projects & Apps/i })).toBeVisible();

    // Navigate to first project detail and verify switcher is present
    await page.locator('a[href*="/proyek/"]').first().click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    await expect(page.locator('#lang-toggle')).toBeVisible();

    // Navigate to /blog and verify switcher is hidden and blog remains Indonesian
    await page.goto('/blog');
    await expect(page.locator('#lang-toggle')).toHaveCount(0);
    await expect(page.locator('html')).toHaveAttribute('lang', 'id');
    await expect(page.getByRole('heading', { level: 1, name: /Catatan & Tulisan/i })).toBeVisible();

    // Return to / and switch back to ID
    await page.goto('/');
    await expect(page.locator('#lang-toggle')).toHaveText('ID');
    await page.locator('#lang-toggle').click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('html')).toHaveAttribute('lang', 'id');
    await expect(page.locator('#lang-toggle')).toHaveText('EN');
});
