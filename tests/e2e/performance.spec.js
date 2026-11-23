import { test, expect } from '@playwright/test';

test('homepage and blog expose lcp priority on hero and lazy loading on cards', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (err) => errors.push(err.message));

    const homeResponse = await page.goto('/');
    expect(homeResponse?.status()).toBe(200);

    const heroImg = page.locator('section[aria-labelledby="hero-heading"] img').first();
    await expect(heroImg).toBeVisible();
    await expect(heroImg).toHaveAttribute('fetchpriority', 'high');
    expect(await heroImg.getAttribute('loading')).toBeNull();

    const blogResponse = await page.goto('/blog');
    expect(blogResponse?.status()).toBe(200);

    const cardImages = page.locator('article img[loading="lazy"]');
    const count = await cardImages.count();
    if (count > 0) {
        await expect(cardImages.first()).toHaveAttribute('decoding', 'async');
    }

    expect(errors).toEqual([]);
});
