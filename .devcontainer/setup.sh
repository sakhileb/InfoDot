#!/bin/bash
set -e

echo "==> Setting up InfoDot dev environment..."

# ── PostgreSQL ──────────────────────────────────────────────────────────────
echo "==> Configuring PostgreSQL..."
sudo service postgresql start
sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'postgres';" 2>/dev/null || true
sudo -u postgres createdb infodot 2>/dev/null || echo "Database 'infodot' already exists."

# ── PHP Extensions ──────────────────────────────────────────────────────────
echo "==> Installing PHP extensions..."
sudo apt-get update -q
sudo apt-get install -y -q \
  php8.4-pgsql \
  php8.4-redis \
  php8.4-gd \
  php8.4-zip \
  php8.4-bcmath \
  php8.4-intl \
  php8.4-mbstring \
  php8.4-xml \
  php8.4-curl \
  php8.4-pcov

# ── Composer ────────────────────────────────────────────────────────────────
echo "==> Installing Composer dependencies..."
if [ -f "composer.json" ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# ── Node / npm ──────────────────────────────────────────────────────────────
echo "==> Installing Node dependencies..."
if [ -f "package.json" ]; then
  npm install
fi

# ── Laravel environment ─────────────────────────────────────────────────────
echo "==> Configuring Laravel..."
if [ ! -f ".env" ]; then
  cp .env.example .env
fi

php artisan key:generate --no-interaction 2>/dev/null || true

# Run migrations (safe — will skip if already run)
php artisan migrate --no-interaction --force 2>/dev/null || echo "Migration skipped (check DB connection)."

# ── Claude Code CLI ─────────────────────────────────────────────────────────
echo "==> Installing Claude Code..."
npm install -g @anthropic-ai/claude-code

# ── Git config ──────────────────────────────────────────────────────────────
echo "==> Configuring git..."
git config --global --add safe.directory /workspaces/InfoDot 2>/dev/null || true

echo ""
echo "✅  InfoDot dev environment ready."
echo ""
echo "   Next steps:"
echo "   1. Run: claude login   (link your Claude account)"
echo "   2. Run: php artisan serve   (start Laravel on :8000)"
echo "   3. Run: npm run dev   (start Vite on :5173)"
echo "   4. Run: php artisan reverb:start   (start WebSockets on :8080)"
echo ""
