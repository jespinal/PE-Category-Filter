# Release Automation Scripts

This directory contains the complete release automation system for the PE Category Filter WordPress plugin. These scripts handle everything from version management to WordPress.org submission preparation.

## Scripts Overview

### 🔧 Core Automation Scripts

#### `update-version.php`
**Purpose**: Centralized version management and updates across all plugin files.

**Features**:
- Updates plugin header version in main file
- Updates readme.txt stable tag and changelog
- Updates Constants.php VERSION constant
- Updates test files and documentation
- Semantic version validation
- Comprehensive error handling

**Usage**:
```bash
php scripts/update-version.php 2.1.0
```

#### `build-distribution.sh`
**Purpose**: Creates WordPress.org ready distribution packages.

**Features**:
- Excludes development files (.git, tests, docs, CI configs, cache files)
- Performs production composer install (--no-dev --optimize-autoloader)
- Generates ZIP and TAR.GZ packages
- Creates checksums and build metadata
- Maintains clean build directory structure

**Usage**:
```bash
./scripts/build-distribution.sh [version]
composer run build
```

#### `release.sh`
**Purpose**: Complete release pipeline orchestration.

**Features**:
- Interactive and automated release modes
- Version auto-increment (patch/minor/major)
- Quality checks (PHPCS, PHPStan, PHPUnit)
- Changelog management
- Git operations (commit, tag, push)
- Distribution building
- Dry-run mode for testing

**Usage**:
```bash
# Interactive mode
./scripts/release.sh

# Automated modes
./scripts/release.sh --patch    # 2.0.0 → 2.0.1
./scripts/release.sh --minor    # 2.0.0 → 2.1.0
./scripts/release.sh --major    # 2.0.0 → 3.0.0

# Composer shortcuts
composer run release:patch
composer run release:minor
composer run release:major
```

#### `verify-release.sh`
**Purpose**: WordPress.org compliance validation and quality assurance.

**Features**:
- ZIP integrity verification
- WordPress plugin header validation
- Required files presence check
- Development files exclusion verification
- File permissions validation
- Package size optimization analysis
- PHP syntax validation
- Composer autoloader verification

**Usage**:
```bash
./scripts/verify-release.sh -v 2.0.0
composer run verify
```

## 🚀 Quick Start

### Standard Release Workflow
```bash
# 1. Make your changes and commit them
git add .
git commit -m "feat: add new functionality"

# 2. Run a patch release (most common)
composer run release:patch

# 3. Verify the release package
composer run verify
```

### Manual Build Only
```bash
# Build distribution without releasing
composer run build

# Check what was built
ls -la build/
```

### Development Testing
```bash
# Test release pipeline without making changes
./scripts/release.sh --dry-run

# Verify existing package
./scripts/verify-release.sh -v $(php -r "require 'src/Core/Constants.php'; echo \PavelEspinal\WpPlugins\PECategoryFilter\Core\Constants::VERSION;")
```

## 📋 Requirements

### System Dependencies
- **PHP 7.4+**: For update-version.php script
- **Bash**: For shell scripts
- **Git**: For version control operations
- **Composer**: For dependency management
- **zip/unzip**: For package creation
- **rsync**: For efficient file operations

### WordPress.org Submission
All scripts generate packages that comply with WordPress.org requirements:
- No development files included
- Proper plugin headers
- Optimized autoloader
- Correct file permissions
- Size optimization

## 🔗 Integration

### Composer Scripts
The scripts are integrated with composer for easy access:

```json
{
  "scripts": {
    "release": "./scripts/release.sh",
    "release:patch": "./scripts/release.sh --patch",
    "release:minor": "./scripts/release.sh --minor",
    "release:major": "./scripts/release.sh --major",
    "build": "./scripts/build-distribution.sh",
    "verify": "./scripts/verify-release.sh",
    "version:show": "php -r \"require 'src/Core/Constants.php'; echo \\PavelEspinal\\\\WpPlugins\\\\PECategoryFilter\\\\Core\\\\Constants::VERSION . PHP_EOL;\""
  }
}
```

### Git Hooks
Scripts handle git operations automatically:
- Commits version updates
- Creates semantic version tags
- Pushes to origin with tags

### CI/CD Integration
Scripts respect CI environments:
- Can run in automated mode
- Provide proper exit codes
- Generate machine-readable output

## 📁 Generated Files

### Build Directory Structure
```
build/
├── pe-category-filter-v2.0.0.zip     # WordPress.org package
├── pe-category-filter-v2.0.0.tar.gz  # Alternative format
├── checksums-v2.0.0.txt              # File integrity hashes
└── build-info-v2.0.0.txt             # Build metadata
```

### Metadata Files
- **checksums-*.txt**: SHA256 hashes for package verification
- **build-info-*.txt**: Build timestamp, version, and system info

## 🛠️ Troubleshooting

### Common Issues

**Permission Errors**:
```bash
chmod +x scripts/*.sh
```

**Missing Dependencies**:
```bash
# Ubuntu/Debian
sudo apt-get install zip unzip rsync

# macOS
brew install zip rsync
```

**Git Authentication**:
Ensure you have push access to the repository and proper Git configuration.

**Version Conflicts**:
The scripts validate semantic versioning and prevent downgrades automatically.

### Debug Mode
Most scripts support verbose output:
```bash
./scripts/release.sh --verbose
./scripts/verify-release.sh --verbose
```

## 📚 Documentation

This README provides complete documentation for the release automation system. For additional context about the plugin itself, see the main project README.md.

---

*Last updated: October 8, 2025*
*Part of PE Category Filter v2.0.0 release automation system*
