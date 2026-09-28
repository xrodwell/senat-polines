#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

if ! command -v php &>/dev/null; then
    echo "❌ PHP not found. Install via: sudo dnf install -y php php-cli php-mbstring php-xml php-curl php-zip composer"
    exit 1
fi

if ! command -v composer &>/dev/null; then
    echo "❌ Composer not found. Install via: sudo dnf install -y composer"
    exit 1
fi

if [ ! -d "vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install
fi

echo "🚀 Starting Senat Polines on http://127.0.0.1:8000 ..."
php artisan serve
