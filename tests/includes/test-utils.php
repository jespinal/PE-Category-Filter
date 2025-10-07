<?php
// Minimal test utilities for unit tests that rely on a subset of WordPress APIs.
//
// Purpose of this file:
// - Provide tiny "shims" (stand-in implementations) for a few WordPress
//   functions and classes so unit tests can run without bootstrapping the
//   entire WordPress test suite.
// - Provide a small mock registry (pecf_register_wp_function_mock) that
//   tests can use to register callbacks for WordPress functions. When a
//   mock is registered we dynamically create a real global function that
//   forwards calls to the registered callback. This avoids trying to
//   stringify/serialize Closure objects (which causes the "Closure could
//   not be converted to string" errors seen in the integration tests).

// Define HOUR_IN_SECONDS if missing (used by repositories)
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 3600 );
}

// Provide a minimal WP_Query class for tests that mock or type-hint it.
if ( ! class_exists( '\WP_Query' ) ) {
    class WP_Query {
        public function is_main_query() {
            return true;
        }

        public function is_home() {
            return true;
        }

        public function set( $key, $value ) {
            // Intentionally empty for tests. Mocks will override behavior.
        }
    }
}

// Provide simple wp_cache_delete if absent (used in tests' teardown)
if ( ! function_exists( 'wp_cache_delete' ) ) {
    function wp_cache_delete( $key, $group = '', $global_groups = null ) {
        if ( isset( $GLOBALS['__pecf_cache'] ) && isset( $GLOBALS['__pecf_cache'][ $group ] ) && isset( $GLOBALS['__pecf_cache'][ $group ][ $key ] ) ) {
            unset( $GLOBALS['__pecf_cache'][ $group ][ $key ] );
            return true;
        }

        return false;
    }
}

// Minimal absint implementation for tests
if ( ! function_exists( 'absint' ) ) {
    function absint( $maybe ) {
        // Mirror WordPress absint behaviour: cast to int and return absolute
        return abs( intval( $maybe ) );
    }
}

// Minimal object cache helpers
if ( ! function_exists( 'wp_cache_get' ) ) {
    function wp_cache_get( $key, $group = '' ) {
        if ( ! isset( $GLOBALS['__pecf_cache'] ) ) {
            return false;
        }
        if ( ! isset( $GLOBALS['__pecf_cache'][ $group ] ) ) {
            return false;
        }
        if ( ! isset( $GLOBALS['__pecf_cache'][ $group ][ $key ] ) ) {
            return false;
        }

        return $GLOBALS['__pecf_cache'][ $group ][ $key ];
    }
}

if ( ! function_exists( 'wp_cache_set' ) ) {
    function wp_cache_set( $key, $value, $group = '', $expire = 0 ) {
        if ( ! isset( $GLOBALS['__pecf_cache'] ) ) {
            $GLOBALS['__pecf_cache'] = array();
        }
        if ( ! isset( $GLOBALS['__pecf_cache'][ $group ] ) ) {
            $GLOBALS['__pecf_cache'][ $group ] = array();
        }
        $GLOBALS['__pecf_cache'][ $group ][ $key ] = $value;
        return true;
    }
}
// Simple in-memory option store for unit tests.
// Functions will consult the mock registry first (if a test registered a
// mock). If no mock is present they use a lightweight in-memory store.
if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value ) {
        // If a test registered a mock, call it. But if the mock returns
        // boolean false (many tests register lightweight mocks that return
        // false for non-matching options) we fall back to the default
        // in-memory behavior so mocks do not leak across tests and prevent
        // later tests from persisting options.
        if ( isset( $GLOBALS['__pecf_wp_function_mocks'][ 'update_option' ] ) ) {
            $mockResult = call_user_func( $GLOBALS['__pecf_wp_function_mocks'][ 'update_option' ], $option, $value );
            // If the mock indicates failure explicitly with false, treat that
            // as "no-op for this option" and fall back to the real shim so
            // other tests aren't blocked by earlier mocks.
            if ( $mockResult !== false ) {
                // Persist into the in-memory store as well so later tests
                // which read options directly (via get_option) will see the
                // expected values. Many unit tests register lightweight
                // mocks that return true to indicate success but do not
                // actually persist data; persisting here avoids cross-test
                // leakage causing fragile failures.
                if ( ! isset( $GLOBALS['__pecf_options'] ) ) {
                    $GLOBALS['__pecf_options'] = array();
                }
                $GLOBALS['__pecf_options'][ $option ] = $value;
                return $mockResult;
            }
            // otherwise continue to fall through and persist the option
            // in the in-memory store.
        }

        if ( ! isset( $GLOBALS['__pecf_options'] ) ) {
            $GLOBALS['__pecf_options'] = array();
        }
        $GLOBALS['__pecf_options'][ $option ] = $value;
        return true;
    }
}

if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) {
        if ( isset( $GLOBALS['__pecf_wp_function_mocks'][ 'delete_option' ] ) ) {
            return call_user_func( $GLOBALS['__pecf_wp_function_mocks'][ 'delete_option' ], $option );
        }

        if ( isset( $GLOBALS['__pecf_options'][ $option ] ) ) {
            unset( $GLOBALS['__pecf_options'][ $option ] );
        }
        return true;
    }
}

// Provide get_option so tests and repositories can read values from the
// same in-memory store. Returns false if option missing (mirrors WP
// behavior).
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        // If the option was directly stored in the in-memory options
        // (via update_option/add_option), prefer that value. This ensures
        // tests that write options directly are not affected by previously
        // registered mocks which may leak across tests.
        if ( isset( $GLOBALS['__pecf_options'][ $option ] ) ) {
            return $GLOBALS['__pecf_options'][ $option ];
        }

        // If a mock is registered and there is no in-memory value, call
        // the mock. Tests intentionally mock get_option in some cases
        // (for example to force a false return), so we respect that when
        // no real value exists.
        if ( isset( $GLOBALS['__pecf_wp_function_mocks'][ 'get_option' ] ) ) {
            return call_user_func( $GLOBALS['__pecf_wp_function_mocks'][ 'get_option' ], $option );
        }

        return $default === false ? false : $default;
    }
}

// Mock registry helper. Tests register a mock with this function and the
// registry will store the callable. We also dynamically create a real
// global function of that name (if it doesn't exist) which forwards to
// the registered callable. This avoids embedding Closure objects as
// strings in eval() calls, which triggers the "Closure could not be
// converted to string" error.
if ( ! function_exists( 'pecf_register_wp_function_mock' ) ) {
    function pecf_register_wp_function_mock( string $functionName, callable $callback ): void {
        if ( ! isset( $GLOBALS['__pecf_wp_function_mocks'] ) ) {
            $GLOBALS['__pecf_wp_function_mocks'] = array();
        }
        $GLOBALS['__pecf_wp_function_mocks'][ $functionName ] = $callback;

        // Create a forwarding function in global namespace that uses the
        // registry value. We use eval to define the function body, but we
        // do NOT attempt to stringify the Closure; the function looks up
        // the callback from the global registry at runtime.
        if ( ! function_exists( $functionName ) ) {
            $safeName = $functionName; // keep simple variable for interpolation
            eval("function {$safeName}() { \$args = func_get_args(); return call_user_func_array(\$GLOBALS['__pecf_wp_function_mocks']['{$safeName}'], \$args); }");
        }
    }
}

// Minimal helper to ensure tests that expect WP constants/functions run.
if ( ! function_exists( 'wp_get_current_user' ) ) {
    function wp_get_current_user() {
        return null;
    }
}

// Provide a small set of additional shims used by integration tests. These
// are intentionally minimal and only implement the behavior the tests rely
// on. If you need more complete WP behavior, run the integration tests
// against the real WP test-suite instead.

// Define the plugin file constant expected by some tests. In the real
// plugin this constant is defined in the main plugin file. Tests and
// production code reference the plain `PE_CATEGORY_FILTER_PLUGIN_FILE`
// constant (not namespaced), so we define that here when running tests
// outside of the real plugin bootstrap.
if ( ! defined( 'PE_CATEGORY_FILTER_PLUGIN_FILE' ) ) {
    define( 'PE_CATEGORY_FILTER_PLUGIN_FILE', __DIR__ );
}

// plugin_basename used by tests to compute plugin rows; a very small shim
if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return basename( (string) $file );
    }
}

// Minimal add_settings_section used by SettingsPage->registerSettings()
if ( ! function_exists( 'add_settings_section' ) ) {
    function add_settings_section( $id, $title, $callback, $page ) {
        if ( ! isset( $GLOBALS['__pecf_settings_sections'] ) ) {
            $GLOBALS['__pecf_settings_sections'] = [];
        }
        $GLOBALS['__pecf_settings_sections'][ $page ][ $id ] = [ 'title' => $title, 'callback' => $callback ];
        return true;
    }
}

// Minimal esc_url used in addActionLinks helper
if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return filter_var( (string) $url, FILTER_SANITIZE_URL );
    }
}

// Minimal internationalization and escaping helpers used by admin UI.
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_attr__' ) ) {
    function esc_attr__( $text, $domain = 'default' ) {
        return $text;
    }
}

// Namespaced wrappers for functions that are called from namespaced code
// (some classes call functions without a leading backslash). We create
// small forwarding functions inside the plugin's namespaces by using
// an eval'd namespace block. This avoids inserting a real `namespace`
// statement into this file (which already contains code) while still
// producing valid, parseable PHP that forwards to the global shims.
//
// Each eval string declares the namespace and the function which simply
// delegates to the global implementation (prefixed with a backslash).
// We only declare the wrapper if it doesn't already exist.
if ( ! function_exists( 'PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\add_settings_section' ) ) {
    eval('namespace PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin { function add_settings_section($id, $title, $callback, $page) { return \\add_settings_section($id, $title, $callback, $page); } }');
}

if ( ! function_exists( 'PavelEspinal\\WpPlugins\\PECategoryFilter\\WordPress\\esc_url' ) ) {
    eval('namespace PavelEspinal\\WpPlugins\\PECategoryFilter\\WordPress { function esc_url($url) { return \\esc_url($url); } }');
}

// Namespaced wrappers for Admin i18n/escaping functions used in views and
// SettingsPage. These forward to the global implementations above.
if ( ! function_exists( 'PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\__' ) ) {
    eval('namespace PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin { function __($text, $domain = "default") { return \\__($text, $domain); } }');
}

if ( ! function_exists( 'PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\esc_html__' ) ) {
    eval('namespace PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin { function esc_html__($text, $domain = "default") { return \\esc_html__($text, $domain); } }');
}

if ( ! function_exists( 'PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin\\esc_attr__' ) ) {
    eval('namespace PavelEspinal\\WpPlugins\\PECategoryFilter\\Admin { function esc_attr__($text, $domain = "default") { return \\esc_attr__($text, $domain); } }');
}

// Simple is_admin shim (integration tests expect false)
if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        return false;
    }
}

// Simple add_action/add_filter/register_activation_hook/register_deactivation_hook
// shims — they record the hooks in a global so tests can inspect them if
// needed; they do not attempt to integrate with a full hooks system.
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        if ( ! isset( $GLOBALS['__pecf_hooks'] ) ) {
            $GLOBALS['__pecf_hooks'] = [];
        }
        $GLOBALS['__pecf_hooks'][ $hook ][] = $callback;
        return true;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        return add_action( $hook, $callback, $priority, $accepted_args );
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {
        if ( ! isset( $GLOBALS['__pecf_activation_hooks'] ) ) {
            $GLOBALS['__pecf_activation_hooks'] = [];
        }
        $GLOBALS['__pecf_activation_hooks'][] = $callback;
        return true;
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {
        if ( ! isset( $GLOBALS['__pecf_deactivation_hooks'] ) ) {
            $GLOBALS['__pecf_deactivation_hooks'] = [];
        }
        $GLOBALS['__pecf_deactivation_hooks'][] = $callback;
        return true;
    }
}

// Minimal add_option used by activation code in tests
if ( ! function_exists( 'add_option' ) ) {
    function add_option( $option, $value = '' ) {
        if ( isset( $GLOBALS['__pecf_wp_function_mocks'][ 'add_option' ] ) ) {
            return call_user_func( $GLOBALS['__pecf_wp_function_mocks'][ 'add_option' ], $option, $value );
        }

        if ( ! isset( $GLOBALS['__pecf_options'] ) ) {
            $GLOBALS['__pecf_options'] = [];
        }
        if ( isset( $GLOBALS['__pecf_options'][ $option ] ) ) {
            return false;
        }
        $GLOBALS['__pecf_options'][ $option ] = $value;
        return true;
    }
}

// Minimal cache flush
if ( ! function_exists( 'wp_cache_flush' ) ) {
    function wp_cache_flush() {
        if ( isset( $GLOBALS['__pecf_cache'] ) ) {
            $GLOBALS['__pecf_cache'] = [];
        }
        return true;
    }
}

// Provide a stub for tests_add_filter when running outside WP test-suite
if ( ! function_exists( 'tests_add_filter' ) ) {
    function tests_add_filter( $hook, $callback ) {
        // For unit tests running without WP test-suite, just invoke the callback
        // immediately if it's callable. Integration tests will use the real hook.
        if ( is_callable( $callback ) ) {
            $callback();
        }
    }
}
