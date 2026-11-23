import fs from 'node:fs';
import path from 'node:path';
import { test, expect } from '@playwright/test';

function findFirstWebp(dir, baseDir = dir) {
    if (!fs.existsSync(dir)) {
        return null;
    }

    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            const found = findFirstWebp(fullPath, baseDir);
            if (found) {
                return found;
            }
        } else if (entry.isFile() && entry.name.endsWith('-large.webp')) {
            return path.relative(baseDir, fullPath).replace(/\\/g, '/');
        }
    }

    return null;
}

test('imported WebP media is served via /storage/ and renders in browser', async ({ page }) => {
    const mediaRoot = path.resolve(process.cwd(), 'storage/app/public/media');
    const relativeWebp = findFirstWebp(mediaRoot);

    expect(relativeWebp).not.toBeNull();

    const mediaUrl = `/storage/media/${relativeWebp}`;
    const response = await page.request.get(mediaUrl);

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('image/webp');

    await page.goto('/');
    await page.evaluate((url) => {
        const img = document.createElement('img');
        img.id = 'e2e-webp-check';
        img.src = url;
        img.loading = 'lazy';
        document.body.appendChild(img);
    }, mediaUrl);

    const img = page.locator('#e2e-webp-check');
    await expect(img).toBeVisible();
    const naturalWidth = await img.evaluate((el) => el.naturalWidth);
    expect(naturalWidth).toBeGreaterThan(0);
});
