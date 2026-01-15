# Security Headers Configuration Guide

**Date**: January 15, 2026  
**Project**: InfoDot Laravel 11  
**Phase**: 14 - Security Hardening

---

## Overview

This guide documents the security headers and HTTPS configuration implemented for the InfoDot Laravel 11 application.

---

## Implemented Security Headers

### 1. Strict-Transport-Security (HSTS)

**Header**: `Strict-Transport-Security: max-age=31536000; includeSubDomains`

**Purpose**: Forces browsers to use HTTPS for all connections to the domain.

**Configuration**:
- Max age: 1 year (31536000 seconds)
- Includes subdomains
- Prevents downgrade attacks

**Benefits**:
- Protects against man-in-the-middle attacks
- Prevents protocol downgrade attacks
- Improves security posture

---

### 2. X-Frame-Options

**Header**: `X-Frame-Options: SAMEORIGIN`

**Purpose**: Prevents clickjacking attacks by controlling whether the page can be embedded in frames.

**Configuration**:
- `SAMEORIGIN`: Only allows framing from the same origin

**Benefits**:
- Prevents clickjacking attacks
- Protects against UI redressing
- Maintains user trust

---

### 3. X-Content-Type-Options

**Header**: `X-Content-Type-Options: nosniff`

**Purpose**: Prevents browsers from MIME-sniffing responses away from the declared content-type.

**Configuration**:
- `nosniff`: Blocks MIME type sniffing

**Benefits**:
- Prevents MIME confusion attacks
- Ensures content is interpreted correctly
- Reduces attack surface

---

### 4. X-XSS-Protection

**Header**: `X-XSS-Protection: 1; mode=block`

**Purpose**: Enables browser's built-in XSS protection (legacy browsers).

**Configuration**:
- Enabled with blocking mode
- Stops page rendering if XSS detected

**Benefits**:
- Additional XSS protection layer
- Supports older browsers
- Defense in depth

**Note**: Modern browsers rely on Content-Security-Policy instead.

---

### 5. Referrer-Policy

**Header**: `Referrer-Policy: strict-origin-when-cross-origin`

**Purpose**: Controls how much referrer information is sent with requests.

**Configuration**:
- Full URL for same-origin requests
- Only origin for cross-origin requests
- No referrer for downgrade (HTTPS to HTTP)

**Benefits**:
- Protects user privacy
- Prevents information leakage
- Maintains functionality

---

### 6. Permissions-Policy

**Header**: `Permissions-Policy: geolocation=(), microphone=(), camera=()`

**Purpose**: Controls which browser features and APIs can be used.

**Configuration**:
- Geolocation: Disabled
- Microphone: Disabled
- Camera: Disabled

**Benefits**:
- Reduces attack surface
- Protects user privacy
- Prevents unauthorized access

**Customization**: Add features as needed:
```
Permissions-Policy: geolocation=(self), microphone=(self), camera=(self)
```

---

### 7. Content-Security-Policy (CSP)

**Header**: `Content-Security-Policy: [directives]`

**Purpose**: Prevents XSS, clickjacking, and other code injection attacks.

**Current Configuration**:
```
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com data:;
img-src 'self' data: https: blob:;
connect-src 'self' ws: wss:;
frame-ancestors 'self';
base-uri 'self';
form-action 'self';
```

**Directives Explained**:

- `default-src 'self'`: Default policy - only allow resources from same origin
- `script-src`: JavaScript sources
  - `'self'`: Same origin scripts
  - `'unsafe-inline'`: Inline scripts (required for Livewire/Alpine)
  - `'unsafe-eval'`: eval() usage (required for some libraries)
  - `https://cdn.jsdelivr.net`: CDN for external libraries
- `style-src`: CSS sources
  - `'self'`: Same origin styles
  - `'unsafe-inline'`: Inline styles (required for Tailwind/DaisyUI)
  - `https://fonts.googleapis.com`: Google Fonts
- `font-src`: Font sources
  - `'self'`: Same origin fonts
  - `https://fonts.gstatic.com`: Google Fonts
  - `data:`: Data URIs for fonts
- `img-src`: Image sources
  - `'self'`: Same origin images
  - `data:`: Data URIs
  - `https:`: Any HTTPS image
  - `blob:`: Blob URLs
- `connect-src`: AJAX, WebSocket, EventSource
  - `'self'`: Same origin connections
  - `ws:` / `wss:`: WebSocket connections (Reverb)
- `frame-ancestors 'self'`: Only allow framing from same origin
- `base-uri 'self'`: Restrict base tag URLs
- `form-action 'self'`: Restrict form submission targets

**Benefits**:
- Strong XSS protection
- Prevents code injection
- Controls resource loading
- Reduces attack surface

**Customization for Production**:

Remove `'unsafe-inline'` and `'unsafe-eval'` for stronger security:
1. Use nonces for inline scripts
2. Move inline scripts to external files
3. Replace eval() usage

---

## HTTPS Configuration

### Force HTTPS Middleware

**File**: `app/Http/Middleware/ForceHttps.php`

**Purpose**: Redirects all HTTP requests to HTTPS in production.

**Configuration**:
- Only active in production environment
- 301 permanent redirect
- Preserves request URI

**Benefits**:
- Ensures encrypted connections
- Protects data in transit
- Improves SEO ranking

**Environment Check**:
```php
if (! $request->secure() && app()->environment('production')) {
    return redirect()->secure($request->getRequestUri(), 301);
}
```

---

## CORS Configuration

### Cross-Origin Resource Sharing

**File**: `config/cors.php`

**Purpose**: Controls which origins can access the API.

**Current Configuration**:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
'allowed_origins' => env('CORS_ALLOWED_ORIGINS') ? explode(',', env('CORS_ALLOWED_ORIGINS')) : ['*'],
'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'Origin'],
'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],
'max_age' => 86400,
'supports_credentials' => true,
```

**Production Configuration**:

Add to `.env`:
```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

**Benefits**:
- Controls API access
- Prevents unauthorized origins
- Supports credentials
- Exposes rate limit headers

---

## Middleware Registration

### Laravel 11 Bootstrap Configuration

**File**: `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    // Add HTTPS redirect middleware (production only)
    $middleware->web(prepend: [
        \App\Http\Middleware\ForceHttps::class,
    ]);
    
    // Add security headers middleware to web routes
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

**Order of Execution**:
1. `ForceHttps` - Redirects to HTTPS first
2. Other web middleware (CSRF, sessions, etc.)
3. `SecurityHeaders` - Adds security headers to response

---

## Testing Security Headers

### Manual Testing

**Using cURL**:
```bash
curl -I https://yourdomain.com
```

**Expected Headers**:
```
HTTP/2 200
strict-transport-security: max-age=31536000; includeSubDomains
x-frame-options: SAMEORIGIN
x-content-type-options: nosniff
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: geolocation=(), microphone=(), camera=()
content-security-policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ...
```

### Online Tools

1. **Security Headers**: https://securityheaders.com/
   - Scan your domain
   - Get security rating
   - Receive recommendations

2. **Mozilla Observatory**: https://observatory.mozilla.org/
   - Comprehensive security scan
   - Best practices check
   - Detailed report

3. **SSL Labs**: https://www.ssllabs.com/ssltest/
   - SSL/TLS configuration test
   - Certificate validation
   - Security grade

---

## Environment-Specific Configuration

### Development Environment

**`.env`**:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

**Behavior**:
- HTTPS redirect disabled
- Security headers still applied
- CORS allows all origins (for testing)

### Staging Environment

**`.env`**:
```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.yourdomain.com
CORS_ALLOWED_ORIGINS=https://staging.yourdomain.com
```

**Behavior**:
- HTTPS redirect disabled (handled by load balancer)
- Security headers applied
- CORS restricted to staging domain

### Production Environment

**`.env`**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

**Behavior**:
- HTTPS redirect enabled
- All security headers applied
- CORS restricted to production domains
- Strict security policies

---

## Troubleshooting

### Issue: CSP Blocking Resources

**Symptom**: Console errors about blocked resources

**Solution**:
1. Identify the blocked resource URL
2. Add the domain to appropriate CSP directive
3. Test thoroughly

**Example**:
```php
// Add new CDN
"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.example.com",
```

### Issue: CORS Errors

**Symptom**: API requests failing with CORS errors

**Solution**:
1. Check `CORS_ALLOWED_ORIGINS` in `.env`
2. Verify origin is included
3. Check credentials setting

**Example**:
```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com,https://mobile.yourdomain.com
```

### Issue: Mixed Content Warnings

**Symptom**: HTTPS page loading HTTP resources

**Solution**:
1. Update all resource URLs to HTTPS
2. Use protocol-relative URLs: `//example.com/resource`
3. Check third-party integrations

### Issue: WebSocket Connection Fails

**Symptom**: Reverb WebSocket not connecting

**Solution**:
1. Verify `connect-src` includes `ws:` and `wss:`
2. Check WebSocket server configuration
3. Ensure CORS allows WebSocket origin

---

## Security Best Practices

### 1. Regular Updates
- Review security headers quarterly
- Update CSP as new resources are added
- Monitor security advisories

### 2. Monitoring
- Log CSP violations
- Monitor for security header bypass attempts
- Track HTTPS usage

### 3. Testing
- Test all security headers after deployment
- Verify HTTPS redirect works
- Check CORS configuration

### 4. Documentation
- Document all CSP exceptions
- Maintain list of allowed origins
- Record security decisions

---

## Additional Security Measures

### 1. Subresource Integrity (SRI)

For external scripts, add integrity attribute:
```html
<script src="https://cdn.example.com/library.js"
        integrity="sha384-..."
        crossorigin="anonymous"></script>
```

### 2. Certificate Pinning

Consider implementing certificate pinning for mobile apps.

### 3. Security Monitoring

Implement security monitoring:
- CSP violation reporting
- Failed authentication attempts
- Suspicious activity detection

---

## Compliance

### OWASP Top 10

Security headers address:
- A03:2021 – Injection (CSP)
- A05:2021 – Security Misconfiguration (All headers)
- A07:2021 – Identification and Authentication Failures (HSTS)

### GDPR

Security headers support GDPR compliance:
- Referrer-Policy protects user privacy
- Permissions-Policy controls data access
- HTTPS ensures data protection

### PCI DSS

Security headers support PCI DSS requirements:
- HTTPS for all connections
- Strong security controls
- Protection of cardholder data

---

## Conclusion

The InfoDot Laravel 11 application now has comprehensive security headers configured to protect against common web vulnerabilities. Regular monitoring and updates will ensure continued security.

**Security Rating**: A+ (Expected on securityheaders.com)

---

**Document Version**: 1.0  
**Last Updated**: January 15, 2026  
**Next Review**: April 15, 2026

