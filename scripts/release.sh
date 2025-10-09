#!/bin/bash
################################################################################
# PE Category Filter - Release Orchestrator
# Complete automated release pipeline from version bump to deployment ready
#
# Usage: ./scripts/release.sh [options]
# Options:
#   --patch                 Patch release (X.Y.Z → X.Y.Z+1)
#   --minor                 Minor release (X.Y.Z → X.Y+1.0)
#   --major                 Major release (X.Y.Z → X+1.0.0)
#   -v, --version VERSION   Specific version (overrides auto-increment)
#   --build-only            Build distribution without git operations
#   --dry-run               Show what would be done without executing
#   -y, --yes               Skip confirmations (use with caution)
#   -h, --help              Show this help
#
# Examples:
#   ./scripts/release.sh --patch                    # 2.0.0 → 2.0.1
#   ./scripts/release.sh --minor                    # 2.0.0 → 2.1.0
#   ./scripts/release.sh -v 2.0.1                   # Specific version
#   ./scripts/release.sh --patch --build-only       # Build without git ops
################################################################################

set -e  # Exit on any error

# Default values
RELEASE_TYPE=""
TARGET_VERSION=""
BUILD_ONLY=false
DRY_RUN=false
AUTO_YES=false
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
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

log_step() {
    echo -e "${CYAN}${BOLD}🔄 $1${NC}"
}

show_help() {
    cat << EOF
PE Category Filter - Release Orchestrator

USAGE:
    ./scripts/release.sh [OPTIONS]

OPTIONS:
    --patch                 Patch release (X.Y.Z → X.Y.Z+1)
    --minor                 Minor release (X.Y.Z → X.Y+1.0)
    --major                 Major release (X.Y.Z → X+1.0.0)
    -v, --version VERSION   Specific version (overrides auto-increment)
    --build-only            Build distribution without git operations
    --dry-run               Show what would be done without executing
    -y, --yes               Skip confirmations (use with caution)
    -h, --help              Show this help

EXAMPLES:
    ./scripts/release.sh                            # Interactive mode
    ./scripts/release.sh --patch                    # Patch: 2.0.0 → 2.0.1
    ./scripts/release.sh --minor                    # Minor: 2.0.0 → 2.1.0
    ./scripts/release.sh --major                    # Major: 2.0.0 → 3.0.0
    ./scripts/release.sh -v 2.0.1                   # Specific version
    ./scripts/release.sh --patch --build-only       # Build without git
    ./scripts/release.sh --patch --dry-run          # Show what would happen

DESCRIPTION:
    Automates the complete release process:
    1. 🔍 Pre-flight checks (git status, environment)
    2. 📝 Version calculation and update
    3. 🧪 Quality assurance (PHPCS, PHPStan, Tests)
    4. 📝 Changelog management (interactive)
    5. 📦 Distribution building (WordPress.org ready)
    6. 🏷️  Git operations (commit, tag, push)
    7. ✅ Verification and next steps

EOF
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --patch)
            RELEASE_TYPE="patch"
            shift
            ;;
        --minor)
            RELEASE_TYPE="minor"
            shift
            ;;
        --major)
            RELEASE_TYPE="major"
            shift
            ;;
        -v|--version)
            TARGET_VERSION="$2"
            shift 2
            ;;
        --build-only)
            BUILD_ONLY=true
            shift
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        -y|--yes)
            AUTO_YES=true
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

# Get current version
get_current_version() {
    cd "$PROJECT_ROOT"
    if [[ -f "src/Core/Constants.php" ]]; then
        grep "public const VERSION" src/Core/Constants.php | sed "s/.*'\([^']*\)'.*/\1/"
    else
        log_error "Cannot find Constants.php to determine current version"
        exit 1
    fi
}

# Calculate next version based on type
calculate_next_version() {
    local current="$1"
    local type="$2"
    
    # Parse current version
    IFS='.' read -r major minor patch <<< "$current"
    
    case "$type" in
        "patch")
            echo "$major.$minor.$((patch + 1))"
            ;;
        "minor")
            echo "$major.$((minor + 1)).0"
            ;;
        "major")
            echo "$((major + 1)).0.0"
            ;;
        *)
            log_error "Invalid release type: $type"
            exit 1
            ;;
    esac
}

# Interactive mode
interactive_mode() {
    local current_version
    current_version=$(get_current_version)
    
    echo -e "${BOLD}🚀 PE Category Filter Release Pipeline${NC}"
    echo "=========================================="
    echo
    echo "Current version: ${BOLD}$current_version${NC}"
    echo
    echo "Choose release type:"
    echo "1) Patch (${current_version} → $(calculate_next_version "$current_version" "patch")) - Bug fixes"
    echo "2) Minor (${current_version} → $(calculate_next_version "$current_version" "minor")) - New features"
    echo "3) Major (${current_version} → $(calculate_next_version "$current_version" "major")) - Breaking changes"
    echo "4) Custom version"
    echo "5) Exit"
    echo
    
    while true; do
        read -p "Your choice (1-5): " choice
        case $choice in
            1)
                RELEASE_TYPE="patch"
                TARGET_VERSION=$(calculate_next_version "$current_version" "patch")
                break
                ;;
            2)
                RELEASE_TYPE="minor"
                TARGET_VERSION=$(calculate_next_version "$current_version" "minor")
                break
                ;;
            3)
                RELEASE_TYPE="major"
                TARGET_VERSION=$(calculate_next_version "$current_version" "major")
                break
                ;;
            4)
                while true; do
                    read -p "Enter custom version (X.Y.Z): " custom_version
                    if [[ "$custom_version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
                        TARGET_VERSION="$custom_version"
                        RELEASE_TYPE="custom"
                        break
                    else
                        echo "Invalid format. Use semantic versioning (X.Y.Z)"
                    fi
                done
                break
                ;;
            5)
                echo "Release cancelled."
                exit 0
                ;;
            *)
                echo "Invalid choice. Please select 1-5."
                ;;
        esac
    done
}

# Pre-flight checks
pre_flight_checks() {
    log_step "Running pre-flight checks"
    
    cd "$PROJECT_ROOT"
    
    # Check if we're in the right directory
    if [[ ! -f "pe-category-filter.php" ]]; then
        log_error "Not in PE Category Filter project root"
        exit 1
    fi
    
    # Check git status
    if [[ "$BUILD_ONLY" == false ]]; then
        if ! git diff-index --quiet HEAD --; then
            log_warning "Working directory has uncommitted changes"
            if [[ "$AUTO_YES" == false ]]; then
                read -p "Continue anyway? (y/N): " -n 1 -r
                echo
                if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                    log_info "Release cancelled"
                    exit 0
                fi
            fi
        fi
        
        # Check if we're on a valid branch
        local current_branch
        current_branch=$(git branch --show-current)
        log_info "Current branch: $current_branch"
        
        # Check if origin exists and can push
        if ! git remote get-url origin &>/dev/null; then
            log_warning "No 'origin' remote configured"
        fi
    fi
    
    # Check required tools
    local missing_tools=()
    command -v composer &>/dev/null || missing_tools+=("composer")
    command -v zip &>/dev/null || missing_tools+=("zip")
    command -v php &>/dev/null || missing_tools+=("php")
    
    if [[ ${#missing_tools[@]} -gt 0 ]]; then
        log_error "Missing required tools: ${missing_tools[*]}"
        exit 1
    fi
    
    log_success "Pre-flight checks passed"
}

# Version update step
update_version() {
    log_step "Updating version to $TARGET_VERSION"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[DRY RUN] Would update version to $TARGET_VERSION"
        return
    fi
    
    cd "$PROJECT_ROOT"
    
    # Use our existing version update script
    if [[ -x "scripts/update-version.php" ]]; then
        php scripts/update-version.php "$TARGET_VERSION"
        if [[ $? -ne 0 ]]; then
            log_error "Version update failed"
            exit 1
        fi
    else
        log_error "Version update script not found or not executable"
        exit 1
    fi
    
    log_success "Version updated to $TARGET_VERSION"
}

# Quality assurance checks
quality_checks() {
    log_step "Running quality assurance checks"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[DRY RUN] Would run PHPCS, PHPStan, and tests"
        return
    fi
    
    cd "$PROJECT_ROOT"
    
    # Run PHPCS
    log_info "Running PHPCS..."
    if ! composer run phpcs --quiet; then
        log_error "PHPCS failed - please fix coding standard violations"
        exit 1
    fi
    
    # Run PHPStan
    log_info "Running PHPStan..."
    if ! composer run phpstan --quiet; then
        log_error "PHPStan failed - please fix static analysis issues"
        exit 1
    fi
    
    # Run tests
    log_info "Running test suite..."
    if ! composer run test --quiet; then
        log_error "Tests failed - please fix failing tests"
        exit 1
    fi
    
    log_success "All quality checks passed"
}

# Changelog management
manage_changelog() {
    log_step "Managing changelog"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[DRY RUN] Would prompt for changelog update"
        return
    fi
    
    echo
    echo "📝 Changelog Management"
    echo "======================"
    echo
    echo "Please update the changelog in readme.txt for version $TARGET_VERSION"
    echo "Add entries for:"
    echo "  • New features"
    echo "  • Bug fixes"
    echo "  • Breaking changes (if any)"
    echo
    
    if [[ "$AUTO_YES" == false ]]; then
        read -p "Press Enter when you've updated the changelog..."
    else
        log_info "[AUTO] Skipping changelog prompt"
    fi
    
    log_success "Changelog management completed"
}

# Build distribution
build_distribution() {
    log_step "Building distribution packages"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[DRY RUN] Would build distribution for version $TARGET_VERSION"
        return
    fi
    
    cd "$PROJECT_ROOT"
    
    # Use our distribution builder
    if [[ -x "scripts/build-distribution.sh" ]]; then
        echo "y" | ./scripts/build-distribution.sh -v "$TARGET_VERSION"
        if [[ $? -ne 0 ]]; then
            log_error "Distribution build failed"
            exit 1
        fi
    else
        log_error "Distribution builder script not found or not executable"
        exit 1
    fi
    
    log_success "Distribution packages built"
}

# Git operations
git_operations() {
    if [[ "$BUILD_ONLY" == true ]]; then
        log_info "Skipping git operations (--build-only flag)"
        return
    fi
    
    log_step "Performing git operations"
    
    if [[ "$DRY_RUN" == true ]]; then
        log_info "[DRY RUN] Would commit, tag, and push version $TARGET_VERSION"
        return
    fi
    
    cd "$PROJECT_ROOT"
    
    # Add all changes
    git add .
    
    # Commit
    local commit_message="chore: release v$TARGET_VERSION"
    git commit -m "$commit_message"
    
    # Create tag
    local tag_name="v$TARGET_VERSION"
    git tag "$tag_name"
    
    # Push commit and tag
    git push origin HEAD
    git push origin "$tag_name"
    
    log_success "Git operations completed"
}

# Show next steps
show_results() {
    echo
    echo "🎉 Release v$TARGET_VERSION completed successfully!"
    echo "=================================================="
    echo
    
    if [[ "$DRY_RUN" == true ]]; then
        echo "📋 This was a dry run. No actual changes were made."
        echo
        return
    fi
    
    echo "📦 Build artifacts:"
    if [[ -d "build" ]]; then
        ls -la build/pe-category-filter-v${TARGET_VERSION}.*
    fi
    echo
    
    echo "📋 Next steps:"
    echo "1. 🧪 Test the distribution package:"
    echo "   unzip build/pe-category-filter-v${TARGET_VERSION}.zip -d /tmp/test-plugin"
    echo
    echo "2. 🚀 Submit to WordPress.org:"
    echo "   • Go to: https://wordpress.org/plugins/developers/add/"
    echo "   • Upload: build/pe-category-filter-v${TARGET_VERSION}.zip"
    echo "   • Wait for review (typically 3-7 days)"
    echo
    echo "3. 📢 Announce the release:"
    echo "   • Update project documentation"
    echo "   • Notify users/contributors"
    echo "   • Social media announcement"
    echo
    
    if [[ "$BUILD_ONLY" == false ]]; then
        echo "4. 🏷️  Release info:"
        echo "   • Git tag: v${TARGET_VERSION}"
        echo "   • Commit: $(git rev-parse HEAD)"
        echo "   • Branch: $(git branch --show-current)"
        echo
    fi
    
    echo "✨ Happy releasing! 🚀"
}

# Main execution
main() {
    echo -e "${BOLD}🏗️  PE Category Filter - Release Orchestrator${NC}"
    echo "==============================================="
    echo
    
    # Handle interactive mode if no version specified
    if [[ -z "$TARGET_VERSION" && -z "$RELEASE_TYPE" ]]; then
        interactive_mode
    elif [[ -n "$RELEASE_TYPE" && -z "$TARGET_VERSION" ]]; then
        local current_version
        current_version=$(get_current_version)
        TARGET_VERSION=$(calculate_next_version "$current_version" "$RELEASE_TYPE")
    fi
    
    # Validate we have a target version
    if [[ -z "$TARGET_VERSION" ]]; then
        log_error "No target version specified"
        show_help
        exit 1
    fi
    
    # Show release plan
    echo "📋 Release Plan:"
    echo "   Target version: ${BOLD}$TARGET_VERSION${NC}"
    echo "   Release type: ${BOLD}${RELEASE_TYPE:-custom}${NC}"
    echo "   Build only: ${BOLD}$BUILD_ONLY${NC}"
    echo "   Dry run: ${BOLD}$DRY_RUN${NC}"
    echo
    
    if [[ "$AUTO_YES" == false && "$DRY_RUN" == false ]]; then
        read -p "Continue with this release? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            log_info "Release cancelled"
            exit 0
        fi
        echo
    fi
    
    # Execute release pipeline
    pre_flight_checks
    update_version
    quality_checks
    manage_changelog
    build_distribution
    git_operations
    show_results
}

# Run main function
main "$@"