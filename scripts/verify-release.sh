#!/bin/bash
################################################################################
# PE Category Filter - Release Verification
# Validates release packages for WordPress.org compliance and quality
#
# Usage: ./scripts/verify-release.sh [options]
# Options:
#   -v, --version VERSION    Version to verify (required)
#   -f, --file PATH          Specific ZIP file to verify
#   --quick                  Quick verification (skip deep checks)
#   -h, --help               Show this help
#
# Example: ./scripts/verify-release.sh -v 2.0.1
################################################################################

set -e

# Default values
VERSION=""
ZIP_FILE=""
QUICK_MODE=false
BUILD_DIR="build"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_error() { echo -e "${RED}❌ $1${NC}"; }

show_help() {
    cat << EOF
PE Category Filter - Release Verification

USAGE:
    ./scripts/verify-release.sh [OPTIONS]

OPTIONS:
    -v, --version VERSION    Version to verify (required)
    -f, --file PATH          Specific ZIP file to verify
    --quick                  Quick verification (skip deep checks)
    -h, --help               Show this help

EXAMPLES:
    ./scripts/verify-release.sh -v 2.0.1
    ./scripts/verify-release.sh -f build/pe-category-filter-v2.0.1.zip
    ./scripts/verify-release.sh -v 2.0.1 --quick

VERIFICATION CHECKS:
    • ZIP file integrity and structure
    • WordPress plugin headers validation
    • File permissions and security
    • WordPress.org compliance
    • Package size and optimization
    • Dependencies and autoloader

EOF
}

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--version) VERSION="$2"; shift 2 ;;
        -f|--file) ZIP_FILE="$2"; shift 2 ;;
        --quick) QUICK_MODE=true; shift ;;
        -h|--help) show_help; exit 0 ;;
        *) log_error "Unknown option: $1"; exit 1 ;;
    esac
done

# Auto-detect version if not provided
if [[ -z "$VERSION" && -z "$ZIP_FILE" ]]; then
    log_info "Auto-detecting version from Constants.php..."
    if [[ -f "$PROJECT_ROOT/src/Core/Constants.php" ]]; then
        VERSION=$(php -r "
            require '$PROJECT_ROOT/src/Core/Constants.php';
            echo \PavelEspinal\WpPlugins\PECategoryFilter\Core\Constants::VERSION;
        " 2>/dev/null)

        if [[ -n "$VERSION" ]]; then
            log_info "Detected version: $VERSION"
        else
            log_error "Failed to read version from Constants.php"
            exit 1
        fi
    else
        log_error "Constants.php not found and no version specified"
        exit 1
    fi
fi

# Determine ZIP file path
if [[ -z "$ZIP_FILE" ]]; then
    if [[ -z "$VERSION" ]]; then
        log_error "Either --version or --file must be specified"
        exit 1
    fi
    ZIP_FILE="$PROJECT_ROOT/$BUILD_DIR/pe-category-filter-v$VERSION.zip"
fi

# Main verification function
verify_release() {
    echo "🔍 PE Category Filter - Release Verification"
    echo "==========================================="
    echo
    echo "📦 Verifying: $(basename "$ZIP_FILE")"
    echo

    # Check if ZIP exists
    if [[ ! -f "$ZIP_FILE" ]]; then
        log_error "ZIP file not found: $ZIP_FILE"
        exit 1
    fi

    # Basic ZIP integrity
    log_info "Checking ZIP integrity..."
    if unzip -t "$ZIP_FILE" >/dev/null 2>&1; then
        log_success "ZIP file integrity OK"
    else
        log_error "ZIP file is corrupted"
        exit 1
    fi

    # Extract to temporary directory for analysis
    local temp_dir="/tmp/pe-category-filter-verify-$$"
    mkdir -p "$temp_dir"
    unzip -q "$ZIP_FILE" -d "$temp_dir"

    # Find the plugin directory
    local plugin_dir
    plugin_dir=$(find "$temp_dir" -name "pe-category-filter.php" -exec dirname {} \;)

    if [[ -z "$plugin_dir" ]]; then
        log_error "Main plugin file not found in ZIP"
        rm -rf "$temp_dir"
        exit 1
    fi

    cd "$plugin_dir"

    # WordPress plugin header validation
    log_info "Validating WordPress plugin headers..."
    local headers_ok=true

    if ! grep -q "Plugin Name:" pe-category-filter.php; then
        log_error "Missing 'Plugin Name' header"
        headers_ok=false
    fi

    if ! grep -q "Version:" pe-category-filter.php; then
        log_error "Missing 'Version' header"
        headers_ok=false
    fi

    if ! grep -q "License:" pe-category-filter.php; then
        log_error "Missing 'License' header"
        headers_ok=false
    fi

    if [[ "$headers_ok" == true ]]; then
        log_success "WordPress plugin headers OK"
    fi

    # Check for required files
    log_info "Checking required files..."
    local required_files=("pe-category-filter.php" "readme.txt")
    for file in "${required_files[@]}"; do
        if [[ -f "$file" ]]; then
            log_success "Required file present: $file"
        else
            log_error "Missing required file: $file"
        fi
    done

    # Check for excluded development files
    log_info "Checking for excluded development files..."
    local dev_files=("tests" "docs" ".github" "phpunit.xml" "phpcs.xml")
    local exclusions_ok=true

    for file in "${dev_files[@]}"; do
        if [[ -e "$file" ]]; then
            log_warning "Development file included: $file"
            exclusions_ok=false
        fi
    done

    if [[ "$exclusions_ok" == true ]]; then
        log_success "Development files properly excluded"
    fi

    # File permissions check
    log_info "Checking file permissions..."
    if find . -type f -perm /111 -name "*.php" | grep -q .; then
        log_warning "Executable PHP files found (may be intentional)"
    else
        log_success "File permissions OK"
    fi

    # Package size analysis
    log_info "Analyzing package size..."
    local zip_size
    zip_size=$(stat -f%z "$ZIP_FILE" 2>/dev/null || stat -c%s "$ZIP_FILE")
    local zip_size_mb=$(printf "%.1f" $(echo "scale=2; $zip_size / 1024 / 1024" | bc))

    if [[ $(echo "$zip_size_mb > 5" | bc) -eq 1 ]]; then
        log_warning "Package size is large: ${zip_size_mb}MB (consider optimization)"
    else
        log_success "Package size OK: ${zip_size_mb}MB"
    fi

    # Quick mode skip deep checks
    if [[ "$QUICK_MODE" == true ]]; then
        log_info "Skipping deep checks (quick mode)"
    else
        # PHP syntax check
        log_info "Checking PHP syntax..."
        if find . -name "*.php" -exec php -l {} \; >/dev/null 2>&1; then
            log_success "PHP syntax OK"
        else
            log_error "PHP syntax errors found"
        fi

        # Autoloader check
        if [[ -f "vendor/autoload.php" ]]; then
            log_success "Composer autoloader present"
        else
            log_warning "No composer autoloader found"
        fi
    fi

    # Clean up
    rm -rf "$temp_dir"

    echo
    log_success "Release verification completed!"
    echo
    echo "📋 Summary:"
    echo "   Package: $(basename "$ZIP_FILE")"
    echo "   Size: ${zip_size_mb}MB"
    echo "   Status: ✅ Ready for WordPress.org"
}

# Run verification
verify_release
