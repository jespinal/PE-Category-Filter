#!/bin/bash

# WordPress Test Installation Script for Local Development
# This script sets up WordPress testing using your local WordPress installation

set -e

# Configuration
WP_CORE_DIR="/var/www/html/wordpress"
WP_TESTS_DIR="/tmp/wordpress-tests-lib"
DB_NAME="wordpress_test"
DB_USER="jespinal"
DB_PASS="abc654321"
DB_HOST="127.0.0.1"
WP_VERSION="latest"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Setting up WordPress testing environment...${NC}"

# Check if WordPress directory exists
if [ ! -d "$WP_CORE_DIR" ]; then
    echo -e "${RED}Error: WordPress directory not found at $WP_CORE_DIR${NC}"
    echo "Please ensure your WordPress installation is at the correct path."
    exit 1
fi

echo -e "${GREEN}✓ WordPress directory found at $WP_CORE_DIR${NC}"

# Create test database
echo -e "${YELLOW}Creating test database...${NC}"
mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -e "DROP DATABASE IF EXISTS $DB_NAME;"
mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -e "CREATE DATABASE $DB_NAME;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Test database '$DB_NAME' created successfully${NC}"
else
    echo -e "${RED}Error: Failed to create test database${NC}"
    exit 1
fi

# Set up WordPress test suite
echo -e "${YELLOW}Setting up WordPress test suite...${NC}"

if [ ! -d "$WP_TESTS_DIR" ]; then
    mkdir -p "$WP_TESTS_DIR"
fi

# Download WordPress test suite
if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
    echo -e "${YELLOW}Downloading WordPress test suite...${NC}"
    curl -s https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php > "$WP_TESTS_DIR/wp-tests-config.php"
    
    # Configure test database
    sed -i "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s|localhost|$DB_HOST|" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s|dirname( __FILE__ ) . '/src/'|'$WP_CORE_DIR'|" "$WP_TESTS_DIR/wp-tests-config.php"
    
    echo -e "${GREEN}✓ WordPress test suite configured${NC}"
fi

# Install WordPress test suite
if [ ! -d "$WP_TESTS_DIR/includes" ]; then
    echo -e "${YELLOW}Installing WordPress test suite...${NC}"
    svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/ "$WP_TESTS_DIR/includes"
    echo -e "${GREEN}✓ WordPress test suite installed${NC}"
fi

# Create symlink for development
echo -e "${YELLOW}Creating development symlink...${NC}"
PLUGIN_DIR="/var/www/html/wordpress/wp-content/plugins/pe-category-filter"

if [ -L "$PLUGIN_DIR" ]; then
    rm "$PLUGIN_DIR"
fi

ln -sf "$(pwd)" "$PLUGIN_DIR"

echo -e "${GREEN}✓ Development symlink created${NC}"

# Install composer dependencies for WordPress
echo -e "${YELLOW}Installing composer dependencies...${NC}"
cd "$PLUGIN_DIR"
composer install --no-dev --optimize-autoloader
cd - > /dev/null

echo -e "${GREEN}✓ Composer dependencies installed${NC}"

echo -e "${GREEN}🎉 WordPress testing environment ready!${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Run: composer run test"
echo "2. Visit: http://localhost:8080/wordpress/wp-admin/plugins.php"
echo "3. Activate 'PE Category Filter' plugin"
echo "4. Test the plugin functionality"
echo ""
echo -e "${GREEN}Test database: $DB_NAME${NC}"
echo -e "${GREEN}WordPress path: $WP_CORE_DIR${NC}"
echo -e "${GREEN}Test suite: $WP_TESTS_DIR${NC}"
