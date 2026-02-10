#!/bin/bash
set -e

# Cleanup function to restore vendor directory
cleanup() {
    if [ -d vendor_temp ]; then
        mv vendor_temp vendor
    fi
}
trap cleanup EXIT

# Download phpDocumentor if not present
if [ ! -f phpDocumentor.phar ]; then
    echo "Downloading phpDocumentor..."
    curl -L -o phpDocumentor.phar https://phpdoc.org/phpDocumentor.phar
    chmod +x phpDocumentor.phar
fi

# Rename vendor to avoid Composer crashes in phpDocumentor
if [ -d vendor ]; then
    mv vendor vendor_temp
fi

# Run phpDocumentor
echo "Running phpDocumentor..."
php phpDocumentor.phar run
