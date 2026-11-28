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

    // Verify PDF portfolio link, hero/project images, and footer Proyek link
    const pdfLink = page.getByRole('link', { name: /Unduh Portofolio PDF/i });
    await expect(pdfLink).toBeVisible();
    const pdfHref = await pdfLink.getAttribute('href');
    const pdfRes = await page.request.get(pdfHref);
    expect(pdfRes.status()).toBe(200);

    const brokenImages = await page.evaluate(async () => {
        const imgs = Array.from(document.querySelectorAll('img'));
        for (const img of imgs) {
            img.scrollIntoView();
            if (!img.complete) {
                await new Promise((resolve) => {
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                    setTimeout(resolve, 3000);
                });
            }
        }
        window.scrollTo(0, 0);
        return imgs.filter((img) => !img.complete || img.naturalWidth === 0).length;
    });
    expect(brokenImages).toBe(0);

    await expect(page.locator('footer').getByRole('link', { name: 'Proyek' })).toBeVisible();

    // Tablet breakpoint (768px) overflow check
    await page.setViewportSize({ width: 768, height: 1024 });
    const tabletOverflow = await page.evaluate(() => {
        if (document.documentElement.scrollWidth <= window.innerWidth) return [];
        return Array.from(document.querySelectorAll('*'))
            .filter((el) => el.getBoundingClientRect().right > window.innerWidth + 1)
            .slice(0, 5)
            .map((el) => `${el.tagName}.${el.className} (right=${Math.round(el.getBoundingClientRect().right)})`);
    });
    expect(tabletOverflow).toEqual([]);

    // Theme toggle check (dark -> light -> dark)
    await page.locator('#theme-toggle').click();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
    await page.locator('#theme-toggle').click();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

    await page.setViewportSize({ width: 375, height: 812 });
    const mobileOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(mobileOverflow).toBe(false);

    await page.evaluate(() => window.scrollTo(0, 600));
    const backToTop = page.getByRole('button', { name: 'Kembali ke atas' });
    await expect(backToTop).toBeVisible();
    await backToTop.click();
    await expect.poll(async () => page.evaluate(() => window.scrollY)).toBeLessThan(100);

    // Click project detail link on homepage
    await page.locator('#aplikasi article h3 a').first().click();
    await expect(page).toHaveURL(/\/proyek\/.+/);

    expect(consoleErrors).toEqual([]);
});
