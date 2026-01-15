# Frontend Optimization Guide

## Overview
This document outlines the frontend optimization strategies implemented for the InfoDot platform to improve page load times, reduce bundle sizes, and enhance user experience.

## Build Optimization

### Vite Configuration
The Vite configuration has been optimized for production builds:

#### Key Optimizations
1. **Code Splitting**: Vendor code is split into separate chunks for better caching
2. **Minification**: Terser is used to minify JavaScript with aggressive compression
3. **Tree Shaking**: Unused code is automatically removed
4. **Asset Inlining**: Small assets (< 4KB) are inlined as base64
5. **Console Removal**: Console.log statements are removed in production

#### Build Command
```bash
npm run build
```

This creates optimized production assets in `public/build/`.

### Tailwind CSS Optimization

#### Purging Unused CSS
Tailwind automatically purges unused CSS in production based on the `content` configuration:

```javascript
content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
],
```

#### DaisyUI Optimization
- Only include themes you actually use (light, dark)
- Disable logs in production
- Optimize component generation

#### CSS Size Reduction
- Before optimization: ~500KB
- After optimization: ~50-100KB (90% reduction)

## Image Optimization

### Recommended Tools

#### 1. ImageOptim (Mac) / Squoosh (Web)
For manual image optimization:
- JPEG: 80-85% quality
- PNG: Use TinyPNG or similar
- WebP: Modern format with better compression

#### 2. Laravel Media Library
Already configured with Spatie Media Library for automatic optimization:

```php
// In model
public function registerMediaConversions(Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(300)
        ->height(300)
        ->sharpen(10)
        ->optimize(); // Automatic optimization
        
    $this->addMediaConversion('medium')
        ->width(800)
        ->height(600)
        ->optimize();
}
```

#### 3. Intervention Image
For on-the-fly image processing:

```bash
composer require intervention/image
```

### Image Best Practices

1. **Use Appropriate Formats**
   - Photos: JPEG or WebP
   - Graphics/Icons: SVG or PNG
   - Animations: WebP or optimized GIF

2. **Responsive Images**
   ```html
   <img 
       src="/images/photo.jpg" 
       srcset="/images/photo-300.jpg 300w,
               /images/photo-600.jpg 600w,
               /images/photo-1200.jpg 1200w"
       sizes="(max-width: 600px) 300px,
              (max-width: 1200px) 600px,
              1200px"
       alt="Description"
       loading="lazy"
   >
   ```

3. **Lazy Loading**
   ```html
   <img src="/images/photo.jpg" loading="lazy" alt="Description">
   ```

4. **Image Dimensions**
   Always specify width and height to prevent layout shift:
   ```html
   <img src="/images/photo.jpg" width="800" height="600" alt="Description">
   ```

## JavaScript Optimization

### Code Splitting
Vendor libraries are split into separate chunks:

```javascript
manualChunks: {
    'vendor': [
        'alpinejs',
        '@livewire/livewire',
    ],
}
```

### Lazy Loading Components
Use dynamic imports for large components:

```javascript
// Instead of
import HeavyComponent from './HeavyComponent';

// Use
const HeavyComponent = () => import('./HeavyComponent');
```

### Alpine.js Optimization
1. **Use x-cloak** to prevent flash of unstyled content:
   ```html
   <div x-data="{ open: false }" x-cloak>
       <!-- Content -->
   </div>
   ```

2. **Defer Alpine.js** for non-critical interactions:
   ```html
   <script src="/path/to/alpine.js" defer></script>
   ```

### Livewire Optimization
1. **Lazy Loading**
   ```php
   <livewire:component lazy />
   ```

2. **Defer Loading**
   ```php
   <livewire:component defer />
   ```

3. **Polling Optimization**
   ```php
   // Instead of constant polling
   wire:poll.5s
   
   // Use visible polling
   wire:poll.visible.5s
   ```

## CSS Optimization

### Critical CSS
Extract and inline critical CSS for above-the-fold content:

```html
<style>
    /* Critical CSS for above-the-fold content */
    .header { /* ... */ }
    .hero { /* ... */ }
</style>
```

### Font Loading Optimization

#### 1. Preload Fonts
```html
<link rel="preload" href="/fonts/figtree.woff2" as="font" type="font/woff2" crossorigin>
```

#### 2. Font Display Strategy
```css
@font-face {
    font-family: 'Figtree';
    src: url('/fonts/figtree.woff2') format('woff2');
    font-display: swap; /* Show fallback font immediately */
}
```

#### 3. Subset Fonts
Only include characters you need:
- Latin only: ~30KB
- Latin + Extended: ~50KB
- Full character set: ~100KB+

## Asset Versioning

### Automatic Versioning
Vite automatically versions assets with content hashes:

```html
<!-- Development -->
<script src="http://localhost:5173/@vite/client"></script>

<!-- Production -->
<script src="/build/assets/app-abc123.js"></script>
```

### Cache Headers
Configure in `.htaccess` or web server:

```apache
# Cache static assets for 1 year
<FilesMatch "\.(jpg|jpeg|png|gif|svg|webp|woff|woff2|ttf|css|js)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>

# Cache HTML for 1 hour
<FilesMatch "\.(html|htm)$">
    Header set Cache-Control "max-age=3600, public"
</FilesMatch>
```

## Performance Monitoring

### Lighthouse Metrics
Target scores:
- Performance: 90+
- Accessibility: 95+
- Best Practices: 95+
- SEO: 95+

### Core Web Vitals
- **LCP (Largest Contentful Paint)**: < 2.5s
- **FID (First Input Delay)**: < 100ms
- **CLS (Cumulative Layout Shift)**: < 0.1

### Monitoring Tools
1. **Chrome DevTools**
   - Network tab for asset sizes
   - Performance tab for profiling
   - Lighthouse for audits

2. **WebPageTest**
   - https://www.webpagetest.org
   - Test from multiple locations
   - Detailed waterfall charts

3. **Google PageSpeed Insights**
   - https://pagespeed.web.dev
   - Real-world performance data
   - Optimization suggestions

## CDN Configuration

### Recommended CDN Providers
1. **Cloudflare** (Free tier available)
2. **AWS CloudFront**
3. **Fastly**
4. **BunnyCDN**

### CDN Setup
1. Configure CDN URL in `.env`:
   ```env
   ASSET_URL=https://cdn.yourdomain.com
   ```

2. Update Vite configuration:
   ```javascript
   export default defineConfig({
       base: process.env.ASSET_URL || '/',
       // ...
   });
   ```

3. Upload assets to CDN after build:
   ```bash
   npm run build
   aws s3 sync public/build s3://your-bucket/build --cache-control max-age=31536000
   ```

## Compression

### Gzip/Brotli Compression
Enable in web server configuration:

#### Apache
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

#### Nginx
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
gzip_min_length 1000;

# Brotli (if available)
brotli on;
brotli_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
```

### Pre-compression
Generate compressed files during build:

```bash
# Install compression tools
npm install --save-dev vite-plugin-compression

# Update vite.config.js
import compression from 'vite-plugin-compression';

export default defineConfig({
    plugins: [
        laravel({...}),
        compression({
            algorithm: 'gzip',
            ext: '.gz',
        }),
        compression({
            algorithm: 'brotliCompress',
            ext: '.br',
        }),
    ],
});
```

## Resource Hints

### Preconnect
Connect to external domains early:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://cdn.yourdomain.com">
```

### DNS Prefetch
Resolve DNS for external domains:
```html
<link rel="dns-prefetch" href="https://analytics.google.com">
```

### Preload
Load critical resources early:
```html
<link rel="preload" href="/fonts/figtree.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/build/assets/app.css" as="style">
```

### Prefetch
Load resources for next navigation:
```html
<link rel="prefetch" href="/questions">
<link rel="prefetch" href="/solutions">
```

## Bundle Analysis

### Analyze Bundle Size
```bash
npm install --save-dev rollup-plugin-visualizer

# Update vite.config.js
import { visualizer } from 'rollup-plugin-visualizer';

export default defineConfig({
    plugins: [
        laravel({...}),
        visualizer({
            open: true,
            gzipSize: true,
            brotliSize: true,
        }),
    ],
});

# Build and view analysis
npm run build
```

### Target Bundle Sizes
- Initial JS bundle: < 200KB (gzipped)
- Initial CSS bundle: < 50KB (gzipped)
- Vendor chunk: < 150KB (gzipped)
- Total page weight: < 1MB

## Performance Checklist

### Build Time
- [ ] Vite configuration optimized
- [ ] Tailwind CSS purging enabled
- [ ] Code splitting configured
- [ ] Minification enabled
- [ ] Source maps disabled for production

### Images
- [ ] Images optimized (< 200KB each)
- [ ] Lazy loading implemented
- [ ] Responsive images used
- [ ] WebP format used where supported
- [ ] Image dimensions specified

### JavaScript
- [ ] Vendor code split into separate chunk
- [ ] Console.log removed in production
- [ ] Unused code removed (tree shaking)
- [ ] Alpine.js deferred
- [ ] Livewire lazy loading used

### CSS
- [ ] Unused CSS purged
- [ ] Critical CSS inlined
- [ ] Fonts optimized
- [ ] DaisyUI themes limited

### Caching
- [ ] Asset versioning enabled
- [ ] Cache headers configured
- [ ] CDN configured (optional)
- [ ] Service worker implemented (optional)

### Compression
- [ ] Gzip enabled
- [ ] Brotli enabled (optional)
- [ ] Pre-compression configured (optional)

### Monitoring
- [ ] Lighthouse score > 90
- [ ] Core Web Vitals passing
- [ ] Performance monitoring set up
- [ ] Error tracking configured

## Testing Performance

### Local Testing
```bash
# Build for production
npm run build

# Serve production build
php artisan serve

# Test with Lighthouse
lighthouse http://localhost:8000 --view
```

### Production Testing
```bash
# Test production site
lighthouse https://yourdomain.com --view

# Test from multiple locations
webpagetest https://yourdomain.com
```

## Continuous Optimization

### Regular Audits
- Run Lighthouse monthly
- Monitor Core Web Vitals
- Review bundle sizes
- Check for unused dependencies

### Dependency Updates
```bash
# Check for outdated packages
npm outdated

# Update dependencies
npm update

# Audit for vulnerabilities
npm audit
```

### Performance Budget
Set and enforce performance budgets:

```javascript
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs', '@livewire/livewire'],
                },
            },
        },
        // Warn if chunk exceeds 500KB
        chunkSizeWarningLimit: 500,
    },
});
```

## References

- [Vite Performance Guide](https://vitejs.dev/guide/performance.html)
- [Tailwind CSS Optimization](https://tailwindcss.com/docs/optimizing-for-production)
- [Web.dev Performance](https://web.dev/performance/)
- [Core Web Vitals](https://web.dev/vitals/)
- [Image Optimization](https://web.dev/fast/#optimize-your-images)
