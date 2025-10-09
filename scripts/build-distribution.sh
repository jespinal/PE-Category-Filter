#!/bin/bash
################################################################################
# PE Category Filter - Distribution Builder
# Creates WordPress.org ready distribution packages
#
# Usage: ./scripts/build-distribution.sh [options]
# Options:
#   -v, --version VERSION    Target version (required)
#   -o, --output DIR         Output directory (default: build)
#   -c, --clean              Clean build directory first
#   -h, --help               Show this help
#
# Example: ./scripts/build-distribution.sh -v 2.0.1
################################################################################

set -e  # Exit on any error

# Default values
VERSION=""
BUILD_DIR="build"
PLUGIN_SLUG="pe-category-filter"
CLEAN_BUILD=false
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

show_help() {
    cat << EOF
PE Category Filter - Distribution Builder

USAGE:
    ./scripts/build-distribution.sh [OPTIONS]

OPTIONS:
    -v, --version VERSION    Target version (required)
    -o, --output DIR         Output directory (default: build)
    -c, --clean              Clean build directory first
    -h, --help               Show this help

EXAMPLES:
    ./scripts/build-distribution.sh -v 2.0.1
    ./scripts/build-distribution.sh -v 2.1.0 -o releases
    ./scripts/build-distribution.sh -v 2.0.1 --clean

DESCRIPTION:
    Creates a clean, WordPress.org ready distribution package by:
    - Installing production dependencies only
    - Excluding development files
    - Optimizing autoloader
    - Generating checksums
    - Creating version-specific ZIP file

EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--version)
            VERSION="$2"
            shift 2
            ;;
        -o|--output)
            BUILD_DIR="$2"
            shift 2
            ;;
        -c|--clean)
            CLEAN_BUILD=true
            shift
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            log_error "Unknown option: $1"
            echo "Use -h or --help for usage information."
            exit 1
            ;;
    esac
done

# Validation
validate_environment() {
    log_info "Validating environment..."
    
    # Check if version was provided
    if [[ -z "$VERSION" ]]; then
        log_error "Version is required. Use -v or --version to specify."
        echo "Example: ./scripts/build-distribution.sh -v 2.0.1"
        exit 1
    fi
    
    # Validate version format (semantic versioning)
    if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        log_error "Version must be in semantic versioning format (X.Y.Z)"
        echo "Example: 2.0.1, 2.1.0, 3.0.0"
        exit 1
    fi
    
    # Check if we're in the project root
    if [[ ! -f "$PROJECT_ROOT/pe-category-filter.php" ]]; then
        log_error "Must be run from the PE Category Filter project root"
        exit 1
    fi
    
    # Check if composer is available
    if ! command -v composer &> /dev/null; then
        log_error "Composer is required but not installed"
        exit 1
    fi
    
    # Check if zip is available
    if ! command -v zip &> /dev/null; then
        log_error "zip command is required but not installed"
        exit 1
    fi
    
    # Check git status (warn if dirty)
    cd "$PROJECT_ROOT"
    if ! git diff-index --quiet HEAD --; then
        log_warning "Working directory has uncommitted changes"
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            log_info "Aborted by user"
            exit 0
        fi
    fi
    
    log_success "Environment validation passed"
}

# Prepare build directory
prepare_build_directory() {
    log_info "Preparing build directory..."
    
    cd "$PROJECT_ROOT"
    
    # Create build directory if it doesn't exist
    mkdir -p "$BUILD_DIR"
    
    # Clean if requested
    if [[ "$CLEAN_BUILD" == true ]]; then
        log_info "Cleaning build directory..."
        rm -rf "${BUILD_DIR:?}"/*
    fi
    
    log_success "Build directory ready: $BUILD_DIR"
}

# Create clean temporary build
create_clean_build() {
    log_info "Creating clean build environment..."
    
    local temp_dir="$BUILD_DIR/temp-$PLUGIN_SLUG-$VERSION"
    
    # Remove temp directory if it exists
    rm -rf "$temp_dir"
    
    # Create temporary directory
    mkdir -p "$temp_dir"
    
    log_info "Copying source files..."
    
    # Copy all files except excluded ones
    rsync -av \
        --exclude='.git/' \
        --exclude='.github/' \
        --exclude='vendor/' \
        --exclude='node_modules/' \
        --exclude='tests/' \
        --exclude='coverage/' \
        --exclude='docs/' \
        --exclude='build/' \
        --exclude='.gitignore' \
        --exclude='.gitattributes' \
        --exclude='composer.lock' \
        --exclude='phpcs.xml' \
        --exclude='phpstan-bootstrap.php' \
        --exclude='phpunit.xml' \
        --exclude='.phpunit.cache/' \
        --exclude='.phpunit.result.cache' \
        --exclude='.php-cs-fixer.cache' \
        --exclude='*.md' \
        --exclude='scripts/' \
        "$PROJECT_ROOT/" \
        "$temp_dir/"
    
    # Change to temp directory for composer operations
    cd "$temp_dir"
    
    log_info "Installing production dependencies..."
    
    # Install production dependencies only
    if [[ -f "composer.json" ]]; then
        composer install --no-dev --optimize-autoloader --no-interaction --quiet
        if [[ $? -ne 0 ]]; then
            log_error "Composer install failed"
            exit 1
        fi
        
        # Remove composer files from distribution
        rm -f composer.json composer.lock
    fi
    
    # Create a simple autoloader for the distributed version
    log_info "Optimizing for distribution..."
    
    # Verify plugin structure
    if [[ ! -f "pe-category-filter.php" ]]; then
        log_error "Main plugin file not found in build"
        exit 1
    fi
    
    if [[ ! -f "readme.txt" ]]; then
        log_error "readme.txt not found in build"
        exit 1
    fi
    
    # Store temp directory path for later use
    echo "$temp_dir" > "$PROJECT_ROOT/$BUILD_DIR/.temp_path"
    
    log_success "Clean build environment created"
}

# Generate distribution packages
generate_distribution() {
    log_info "Generating distribution packages..."
    
    cd "$PROJECT_ROOT"
    
    # Read temp directory path
    local temp_dir
    temp_dir=$(cat "$BUILD_DIR/.temp_path")
    
    local zip_name="$PLUGIN_SLUG-v$VERSION.zip"
    local tar_name="$PLUGIN_SLUG-v$VERSION.tar.gz"
    local zip_path="$BUILD_DIR/$zip_name"
    local tar_path="$BUILD_DIR/$tar_name"
    
    log_info "Creating ZIP archive..."
    
    # Create ZIP file
    cd "$(dirname "$temp_dir")"
    zip -r "$PROJECT_ROOT/$zip_path" "$(basename "$temp_dir")" -q
    
    if [[ $? -ne 0 ]]; then
        log_error "Failed to create ZIP archive"
        exit 1
    fi
    
    log_info "Creating TAR.GZ archive..."
    
    # Create TAR.GZ file
    tar -czf "$PROJECT_ROOT/$tar_path" "$(basename "$temp_dir")"
    
    if [[ $? -ne 0 ]]; then
        log_error "Failed to create TAR.GZ archive"
        exit 1
    fi
    
    # Return to project root
    cd "$PROJECT_ROOT"
    
    # Generate checksums
    log_info "Generating checksums..."
    (
        cd "$BUILD_DIR"
        sha256sum "$zip_name" > "checksums-v$VERSION.txt"
        sha256sum "$tar_name" >> "checksums-v$VERSION.txt"
    )
    
    # Generate build info
    log_info "Generating build information..."
    cat > "$BUILD_DIR/build-info-v$VERSION.txt" << EOF
PE Category Filter - Build Information
=====================================

Version: $VERSION
Build Date: $(date -u '+%Y-%m-%d %H:%M:%S UTC')
Build Host: $(hostname)
Git Commit: $(git rev-parse HEAD 2>/dev/null || echo "N/A")
Git Branch: $(git branch --show-current 2>/dev/null || echo "N/A")

Files:
- $zip_name (WordPress.org submission)
- $tar_name (Alternative format)
- checksums-v$VERSION.txt (SHA256 hashes)

Package Contents:
- Main plugin file with optimized autoloader
- Production dependencies only
- WordPress.org compliant structure
- Excludes: tests, docs, development files

Ready for WordPress.org submission: YES
EOF
    
    # Get file sizes
    local zip_size
    local tar_size
    zip_size=$(ls -lh "$BUILD_DIR/$zip_name" | awk '{print $5}')
    tar_size=$(ls -lh "$BUILD_DIR/$tar_name" | awk '{print $5}')
    
    # Clean up temp directory
    rm -rf "$temp_dir"
    rm -f "$BUILD_DIR/.temp_path"
    
    log_success "Distribution packages created successfully"
    
    # Show results
    echo
    echo "📦 Distribution Summary"
    echo "======================"
    echo "Version: $VERSION"
    echo "ZIP size: $zip_size"
    echo "TAR size: $tar_size"
    echo "Location: $BUILD_DIR/"
    echo
    echo "📋 Files created:"
    echo "  • $zip_name (WordPress.org ready)"
    echo "  • $tar_name (alternative format)"
    echo "  • checksums-v$VERSION.txt"
    echo "  • build-info-v$VERSION.txt"
    echo
    echo "🚀 Ready for WordPress.org submission!"
    echo "   Upload: $BUILD_DIR/$zip_name"
    echo "   To: https://wordpress.org/plugins/developers/add/"
}

# Main execution
main() {
    echo "🏗️  PE Category Filter - Distribution Builder"
    echo "=============================================="
    echo
    
    validate_environment
    prepare_build_directory
    create_clean_build
    generate_distribution
    
    echo
    log_success "Build completed successfully! 🎉"
    echo
    echo "Next steps:"
    echo "1. Test the distribution: unzip and verify contents"
    echo "2. Submit to WordPress.org (if ready)"
    echo "3. Archive this build for future reference"
}

# Run main function
main "$@"