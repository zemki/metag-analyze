#!/bin/bash

# Toggle between local and expose URLs for QR code testing

ENV_FILE=".env"

# Check current APP_URL
CURRENT_URL=$(grep "^APP_URL=" $ENV_FILE | cut -d '=' -f2)

echo "Current APP_URL: $CURRENT_URL"
echo ""
echo "Options:"
echo "1) Use local URL (http://metag-analyze.test)"
echo "2) Use expose URL (enter custom URL)"
echo "3) Cancel"
echo ""
read -p "Choose option (1-3): " choice

case $choice in
  1)
    # Set to local
    sed -i.bak 's|^APP_URL=.*|APP_URL=http://metag-analyze.test|' $ENV_FILE
    echo "✓ Set to local: http://metag-analyze.test"
    ;;
  2)
    # Set to custom expose URL
    read -p "Enter your expose URL (e.g., https://abc123.sharedwithexpose.com): " expose_url
    sed -i.bak "s|^APP_URL=.*|APP_URL=$expose_url|" $ENV_FILE
    echo "✓ Set to expose: $expose_url"
    ;;
  3)
    echo "Cancelled"
    exit 0
    ;;
  *)
    echo "Invalid option"
    exit 1
    ;;
esac

# Clear Laravel config cache
php artisan config:clear

echo ""
echo "✓ Config cache cleared"
echo "✓ New APP_URL: $(grep "^APP_URL=" $ENV_FILE | cut -d '=' -f2)"
