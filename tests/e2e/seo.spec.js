import { test, expect } from '@playwright/test';

test('sitemap, rss feed, robots.txt, and legacy 301 redirect work end to end', async ({ page, request }) => {
    const sitemapRes = await request.get('/sitemap.xml');
    expect(sitemapRes.status()).toBe(200);
    expect(sitemapRes.headers()['content-type']).toContain('application/xml');
    const sitemapBody = await sitemapRes.text();
    expect(sitemapBody).toContain('<urlset');
    expect(sitemapBody).toContain('/blog');

    const feedRes = await request.get('/feed');
    expect(feedRes.status()).toBe(200);
    expect(feedRes.headers()['content-type']).toContain('application/rss+xml');
    const feedBody = await feedRes.text();
    expect(feedBody).toContain('<rss version="2.0"');
    expect(feedBody).toContain('<channel>');

    const robotsRes = await request.get('/robots.txt');
    expect(robotsRes.status()).toBe(200);
    const robotsBody = await robotsRes.text();
    expect(robotsBody).toContain('Disallow: /admin');
    expect(robotsBody).toContain('Sitemap:');

    await page.goto('/blog');
    const rssAlternate = page.locator('head link[rel="alternate"][type="application/rss+xml"]');
    await expect(rssAlternate).toHaveAttribute('href', /\/feed$/);

    await page.goto('/rss');
    await expect(page).toHaveURL(/\/feed$/);
});
