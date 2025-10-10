#!/usr/bin/env php
<?php
/**
 * Plugin Version Update Script
 *
 * Updates the plugin version across all necessary files from a single command.
 * This script ensures version consistency across the entire project.
 *
 * Usage: php scripts/update-version.php <new-version>
 * Example: php scripts/update-version.php 2.0.1
 *
 * @package PECF
 * @since 2.0.0
 */

// Ensure we have the correct number of arguments
if ($argc !== 2) {
    echo "❌ Error: Incorrect usage.\n";
    echo "Usage: php scripts/update-version.php <new-version>\n";
    echo "Example: php scripts/update-version.php 2.0.1\n";
    exit(1);
}

$newVersion = $argv[1];
$projectRoot = dirname(__DIR__);

// Validate version format (semantic versioning)
if (!preg_match('/^\d+\.\d+\.\d+$/', $newVersion)) {
    echo "❌ Error: Version must be in semantic versioning format (X.Y.Z)\n";
    echo "Example: 2.0.1, 2.1.0, 3.0.0\n";
    exit(1);
}

echo "🚀 Updating PE Category Filter to version $newVersion...\n\n";

$errors = [];
$updates = [];

/**
 * Update a file with version replacement
 *
 * @param string $file File path
 * @param string $pattern Regex pattern to match
 * @param string $replacement Replacement string
 * @param string $description Human readable description
 * @return bool Success status
 */
function updateFile($file, $pattern, $replacement, $description) {
    global $errors, $updates, $projectRoot;
    
    $fullPath = $projectRoot . '/' . $file;
    
    if (!file_exists($fullPath)) {
        $errors[] = "File not found: $file";
        return false;
    }
    
    $content = file_get_contents($fullPath);
    $originalContent = $content;
    
    $content = preg_replace($pattern, $replacement, $content);
    
    if ($content === null) {
        $errors[] = "Regex error in $file";
        return false;
    }
    
    if ($content === $originalContent) {
        $errors[] = "No changes made to $file - pattern might not match";
        return false;
    }
    
    if (file_put_contents($fullPath, $content) === false) {
        $errors[] = "Failed to write to $file";
        return false;
    }
    
    $updates[] = "✅ $description";
    return true;
}

// 1. Update Constants.php (single source of truth)
updateFile(
    'src/Core/Constants.php',
    "/public const VERSION = '[^']+';/",
    "public const VERSION = '$newVersion';",
    "Updated Constants.php (single source of truth)"
);

// 2. Update main plugin file header (required by WordPress)
updateFile(
    'pe-category-filter.php',
    '/^Version:\s+[\d.]+$/m',
    "Version:       $newVersion",
    "Updated plugin header version in pe-category-filter.php"
);

// 3. Update readme.txt stable tag (required by WordPress.org)
updateFile(
    'readme.txt',
    '/^Stable tag: [\d.]+$/m',
    "Stable tag: $newVersion",
    "Updated stable tag in readme.txt"
);

// 4. Update PHPStan bootstrap fallback
updateFile(
    'phpstan-bootstrap.php',
    "/define\\('PE_CATEGORY_FILTER_VERSION', '[^']+'/",
    "define('PE_CATEGORY_FILTER_VERSION', '$newVersion'",
    "Updated PHPStan bootstrap fallback version"
);

// 5. Update test expectations (they might have hardcoded version checks)
$testFiles = [
    'tests/Integration/Core/PluginTest.php' => "/assertEquals\\('[^']+', \\\$version\\);/",
    'tests/Unit/Repositories/SettingsRepositoryTest.php' => "/'version' => '[^']+',/"
];

foreach ($testFiles as $file => $pattern) {
    if (file_exists($projectRoot . '/' . $file)) {
        $content = file_get_contents($projectRoot . '/' . $file);
        if (preg_match($pattern, $content)) {
            // Update test expectations
            if ($file === 'tests/Integration/Core/PluginTest.php') {
                updateFile($file, $pattern, "assertEquals('$newVersion', \\\$version);", "Updated test version expectation in $file");
            } else {
                updateFile($file, $pattern, "'version' => '$newVersion',", "Updated test version expectation in $file");
            }
        }
    }
}

// Display results
echo "\n📊 Update Summary:\n";
echo str_repeat("=", 50) . "\n";

if (!empty($updates)) {
    foreach ($updates as $update) {
        echo "$update\n";
    }
}

if (!empty($errors)) {
    echo "\n⚠️  Warnings/Errors:\n";
    foreach ($errors as $error) {
        echo "❌ $error\n";
    }
}

// Final instructions
echo "\n🎉 Version update completed!\n";
echo "\n📋 Next steps:\n";
echo "   1. Update CHANGELOG section in readme.txt for version $newVersion\n";
echo "   2. Test the plugin: composer test\n";
echo "   3. Verify version: php -r \"require 'src/Core/Constants.php'; echo \\PavelEspinal\\\\WpPlugins\\\\PECategoryFilter\\\\Core\\\\Constants::VERSION . PHP_EOL;\"\n";
echo "   4. Commit changes: git add . && git commit -m \"chore: bump version to $newVersion\"\n";
echo "   5. Create git tag: git tag v$newVersion\n";
echo "   6. Push: git push && git push --tags\n";

echo "\n✨ Happy releasing!\n";