# WordPress.org Plugin Release Guide

**Purpose:** Manual workflow for releasing PE Category Filter to WordPress.org Plugin Directory

## Overview

This guide follows the official [WordPress.org SVN documentation](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/) for releasing plugin updates. The process uses SVN (Subversion) to deploy clean, production-ready code to the WordPress.org Plugin Directory.

**Key Principle:** SVN is a release system, not a development system. Only push finished, ready-to-release code.

## Prerequisites

- **WordPress.org Account:** Username `your.username` with SVN access
- **SVN Password:** Set at [profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password](https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password)
- **SVN Client:** Command-line `svn` or GUI tool
- **Plugin Status:** Already exists on WordPress.org (not first submission)

## Understanding SVN Structure

Your plugin's SVN repository has three main directories:

```
/assets/          # Screenshots, banners, icons
/tags/            # Stable releases (1.0, 2.0.1, etc.)
/trunk/           # Development/latest version
```

### Important Rules:
- **Trunk:** Contains latest development version, should always be working
- **Tags:** Stable releases only (never put untested code here)
- **Assets:** Screenshots and banners (reduces plugin download size)

## Step-by-Step Release Process

### Step 1: Prepare Your Release

#### 1.1 Update Plugin Version
```bash
# Update version in your development environment
composer run version:update -- X.Y.Z
```

#### 1.2 Build Clean Distribution
```bash
# Create production-ready build
composer run build
```

This creates: `build/pe-category-filter-vX.Y.Z.zip`

#### 1.3 Test the Build
```bash
# Verify build quality
composer run verify
```

### Step 2: Checkout SVN Repository (One-time setup)

```bash
# Create local directory
mkdir pe-category-filter-svn
cd pe-category-filter-svn

# Checkout the SVN repository
svn checkout https://plugins.svn.wordpress.org/pe-category-filter .
```

**Result:** Creates local copy with `/assets/`, `/tags/`, `/trunk/` directories.

### Step 3: Update Trunk with New Code

#### 3.1 Ensure SVN is Up-to-Date
```bash
svn update
```

#### 3.2 Extract New Code to Trunk
```bash
# Remove old trunk contents (keep .svn directory)
rm -rf trunk/*

# Extract new version to trunk (preserves directory structure)
unzip ../build/pe-category-filter-vX.Y.Z.zip
cp -r temp-pe-category-filter-*/* trunk/
rm -rf temp-pe-category-filter-*
```

**Important:** This preserves the plugin's directory structure (src/, assets/, vendor/, etc.) which is essential for proper plugin functionality.

#### 3.3 Update Assets (if changed)
```bash
# Copy new screenshots/banners to assets folder
# (Current directory: pe-category-filter-svn/)
cp trunk/screenshot-*.png assets/
```

#### 3.4 Handle Missing Files
If you see files with `!` status (missing), these were removed by your build process:
```bash
# Remove missing files from SVN tracking
svn status | grep '^!' | awk '{print $2}' | xargs svn remove
```

#### 3.5 Add New Files to SVN
```bash
# Add any new files
svn add trunk/* --force

# Add new assets if any
svn add assets/* --force
```

#### 3.6 Check Status and Commit to Trunk
```bash
# Review what will be committed
svn status

# See differences
svn diff

# Commit changes to trunk
svn commit -m "Update trunk to version X.Y.Z"
```

### Step 4: Create Release Tag

#### 4.1 Copy Trunk to New Tag
```bash
# Create tag from current trunk
svn copy trunk tags/X.Y.Z
```

#### 4.2 Update Stable Tag in readme.txt
```bash
# Edit trunk/readme.txt to update Stable tag field
# Change: Stable tag: OLD_VERSION
# To:     Stable tag: X.Y.Z
svn commit -m "Update stable tag to X.Y.Z"
```

#### 4.3 Commit the New Tag
```bash
# Commit the new tag
svn commit -m "Tag version X.Y.Z"
```

### Step 5: Verification

#### 5.1 Check WordPress.org
- Visit your plugin page: `https://wordpress.org/plugins/pe-category-filter/`
- Verify version number shows `X.Y.Z`
- Check that download link works

#### 5.2 Test Download
```bash
# Test the download from WordPress.org
wget https://downloads.wordpress.org/plugin/pe-category-filter.zip
unzip pe-category-filter.zip
# Verify contents match your release
```

## Complete Release Commands

Here's the full sequence for releasing a new version:

```bash
# 1. Prepare locally
composer run version:update -- X.Y.Z
composer run build
composer run verify

# 2. Update SVN
cd pe-category-filter-svn
svn update

# 3. Update trunk
rm -rf trunk/*
unzip ../build/pe-category-filter-vX.Y.Z.zip
cp -r temp-pe-category-filter-*/* trunk/
rm -rf temp-pe-category-filter-*
cp trunk/screenshot-*.png assets/

# 4. Handle file changes
svn status | grep '^!' | awk '{print $2}' | xargs svn remove  # Remove missing files
svn add trunk/* --force
svn add assets/* --force

# 5. Commit trunk
svn commit -m "Update trunk to version X.Y.Z"

# 6. Create and commit tag
svn copy trunk tags/X.Y.Z
svn commit -m "Tag version X.Y.Z"
```

## Best Practices

### ✅ Do:
- **Always test builds** before pushing to SVN
- **Update trunk first**, then create tags from trunk
- **Use semantic versioning** (2.0.1, 2.1.0, 3.0.0)
- **Include meaningful commit messages**
- **Verify downloads** after release
- **Handle missing files** properly with `svn remove`

### ❌ Don't:
- **Put main plugin file in subdirectory** (breaks downloads)
- **Commit every small change** (SVN is for releases, not development)
- **Upload ZIP files** to SVN (upload individual files)
- **Skip testing** before committing
- **Put development files** in SVN (.git, tests/, docs/, etc.)

## Troubleshooting

### Authentication Issues
```bash
# If authentication fails, include credentials
svn commit -m "Update trunk to version X.Y.Z" --username your.username --password your_password
```

### File Conflicts
```bash
# If conflicts occur
svn status  # Shows conflict files
svn resolve --accept working [filename]
```

### SVN Status Meanings
- `A` = **Added** (new files being added)
- `D` = **Deleted** (files being removed)
- `!` = **Missing** (files that SVN expects but can't find - use `svn remove`)
- `M` = **Modified** (files that have been changed)

### Rollback Bad Release
```bash
# Copy previous working tag to trunk
svn copy tags/PREVIOUS_VERSION trunk --force
svn commit -m "Rollback to version PREVIOUS_VERSION"

# Remove bad tag
svn delete tags/BAD_VERSION
svn commit -m "Remove faulty tag BAD_VERSION"
```

## File Structure Reference

### What Goes in Each Directory:

**`/trunk/`** (Required files):
```
pe-category-filter.php    # Main plugin file
readme.txt               # WordPress.org readme
src/                     # Plugin source code
assets/                  # CSS/JS assets
languages/               # Translation files
vendor/                  # Composer dependencies
```

**`/assets/`** (Optional):
```
screenshot-1.png         # Plugin screenshots
screenshot-2.png
banner-772x250.png       # Header banner
banner-1544x500.png      # High-res header banner
icon-128x128.png         # Plugin icon
icon-256x256.png         # High-res plugin icon
```

**`/tags/`** (Copies of stable releases):
```
1.0/                     # Version 1.0 files
2.0.0/                   # Version 2.0.0 files
2.0.1/                   # Version 2.0.1 files
```

## Integration with Development Workflow

This SVN release process integrates with your existing automation tools:

1. **Development:** Work on feature branches in Git
2. **Testing:** Run CI pipeline
3. **Version Management:** `composer run version:update -- X.Y.Z`
4. **Build:** `composer run build` (creates WordPress.org ready ZIP with auto-version detection)
5. **Quality Check:** `composer run verify` (validates package with accurate size reporting)
6. **Git Release:** `composer run release` (tags in Git)
7. **WordPress.org Release:** Follow this SVN guide
8. **Verification:** Test download and functionality

## Official Resources

- **SVN Documentation:** [developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)
- **readme.txt Guide:** [developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)
- **Plugin Assets:** [developer.wordpress.org/plugins/wordpress-org/plugin-assets/](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
- **SVN Password Setup:** [profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password](https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password)

---

**Remember:** SVN is for releasing finished code. Test thoroughly before committing, and only push when you're ready for users to receive the update.
