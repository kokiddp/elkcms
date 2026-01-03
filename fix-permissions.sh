#!/bin/bash
# Fix ELKCMS Permissions
# This script sets proper ownership and permissions for the ELKCMS project

echo "Fixing permissions for ELKCMS..."

# Get current user UID
USER_ID=$(id -u)
USER_NAME=$(id -un)

echo "Setting ownership to $USER_NAME (UID: $USER_ID)..."
docker exec elkcms_app chown -R $USER_ID:$USER_ID /var/www

echo "Setting base permissions (755)..."
docker exec elkcms_app chmod -R 755 /var/www

echo "Setting full writable permissions for storage (777)..."
docker exec elkcms_app chmod -R 777 /var/www/storage

echo "Setting writable permissions for other Laravel directories (775)..."
docker exec elkcms_app chmod -R 775 /var/www/bootstrap/cache /var/www/public

echo "Configuring git..."
git config core.fileMode false
git config --global --add safe.directory $(pwd)

echo "✅ Permissions fixed!"
echo ""
echo "Summary:"
echo "  - All files owned by: $USER_NAME"
echo "  - Base permissions: 755 (rwxr-xr-x)"
echo "  - Storage directory: 777 (rwxrwxrwx)"
echo "  - Laravel writable: 775 (rwxrwxr-x)"
echo "  - Git filemode tracking: disabled"
