import { test, expect } from '@playwright/test';

test('blog index, search, category/tag filters, and article detail flow without console errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            consoleErrors.push(msg.text());
        }
    });
    page.on('pageerror', (err) => {
        consoleErrors.push(err.message);
    });

    await page.goto('/blog');

    await expect(page).toHaveTitle(/Blog \| Akut Wibowo/);
    await expect(page.getByRole('heading', { level: 1, name: 'Catatan & Tulisan' })).toBeVisible();

    const firstCardImg = page.locator('article img').first();
    await expect(firstCardImg).toBeVisible();
    await expect(firstCardImg).toHaveAttribute('loading', 'lazy');
    await expect(firstCardImg).toHaveAttribute('src', /\.webp$/);

    // Navigate into a real category archive.
    const categoryLink = page.locator('nav[aria-label="Filter berdasarkan kategori"] a[href*="/category/"]').first();
    await expect(categoryLink).toBeVisible();
    await categoryLink.click();
    await expect(page).toHaveURL(/\/category\/[a-z0-9-]+$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Kategori:');

    // Search with an unmatched query and verify empty state + recovery CTA.
    const searchInput = page.locator('#blog-search');
    await searchInput.fill('zzz-tidak-ada-hasil-999');
    await page.getByRole('button', { name: 'Cari' }).click();
    await expect(page.getByText('Tidak ada tulisan yang cocok')).toBeVisible();
    await page.getByRole('link', { name: 'Lihat semua tulisan' }).click();
    await expect(page).toHaveURL(/\/blog$/);

    // Navigate into a tag archive.
    const tagLink = page.locator('aside[aria-labelledby="popular-tags-heading"] a[href*="/tag/"]').first();
    await expect(tagLink).toBeVisible();
    await tagLink.click();
    await expect(page).toHaveURL(/\/tag\/[a-z0-9-]+$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Tag:');

    // Open an article detail at its legacy root /{slug} URL.
    const articleLink = page.locator('article h2 a').first();
    const articleHref = await articleLink.getAttribute('href');
    expect(articleHref).toBeTruthy();
    await articleLink.click();

    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await expect(page.locator('.article-prose')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Kembali ke daftar blog' })).toBeVisible();

    expect(consoleErrors).toEqual([]);
});

test('blog index and detail stay responsive on mobile with zero horizontal overflow', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });

    await page.goto('/blog');
    await expect(page.getByRole('heading', { level: 1, name: 'Catatan & Tulisan' })).toBeVisible();

    const indexOverflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth);
    expect(indexOverflow).toBe(false);

    await page.locator('article h2 a').first().click();
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();

    const detailOverflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth);
    expect(detailOverflow).toBe(false);
});
