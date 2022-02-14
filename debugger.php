<?php
/**
 * Plugin Name: Debugger
 * Description: PHP Debug Window
 * Version: 1.0
 * Author: aChroma Kommunikation
 * Author URI: https://achroma.at
 */

if ( ! class_exists( 'Debugger' ) ) {
	
	class Debugger {

        private static $data;
        public static $var_dump;
        public static $basedir;
        public static $baseurl;

        public static function init() {
            self::$data = array();
            self::$basedir = plugin_dir_path( __FILE__ );
            self::$baseurl = plugin_dir_url( __FILE__ );
            self::$var_dump = false;

            add_action( 'init', array( __CLASS__, 'init_session' ) );
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'loadScripts' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'loadScripts' ) );
            add_action( 'wp_footer', array( __CLASS__, 'outputWindow' ) );
            add_action( 'admin_footer', array( __CLASS__, 'outputWindow' ) );
        }

        public static function init_session() {
            if ( ! session_id() ) {
                session_start();
            }

            if ( isset( $_POST['_debugger_clear_persistent_data'] ) ) {
                self::clear_persistent_data();
            }

            if ( isset( $_SESSION['_debugger_data'] ) ) {
                self::$data = array_merge( self::$data, $_SESSION['_debugger_data'] );
            }
        }

        public static function loadScripts() {
            wp_enqueue_style( 'fontawesome', self::$baseurl . 'lib/fontawesome/css/all.css' );
            wp_enqueue_style( 'highlight', self::$baseurl . 'lib/highlight/styles/monokai-sublime.min.css' );
            wp_enqueue_script( 'highlight', self::$baseurl . 'lib/highlight/highlight.min.js' );
            wp_enqueue_script( 'debugger', self::$baseurl . 'main.js' );
            wp_enqueue_style( 'debugger', self::$baseurl . 'style.css' );
        }

        public static function outputWindow() {
            if ( ! user_can( get_current_user_id(), 'edit_posts' ) ) return;

            if ( sizeof( self::$data ) == 0 ) return;

            ?>

            <div id="debug">
                <form method="POST" class="mb-3">
                    <button type="submit" class="button" name="_debugger_clear_persistent_data" value="true"><?php _e( 'Clear persistent data', 'debugger' ) ?></button>
                </form>

                <?php foreach ( self::$data as $data_block ) : ?>
                    <div class="data-block">
                        <div class="caller"><a href="#"></a><?php print_r( $data_block['caller'] ); ?></div>
                        <pre><code><?php self::$var_dump ? var_dump( $data_block['data'] ) : print_r( $data_block['data'] ); ?></code></pre>
                    </div>

                <?php endforeach; ?>

                <div class="debug-action-bar">
                    <a href="#" class="toggle-size"><i class="fa-solid fa-window-minimize"></i></a>
                    <a href="#" class="toggle-size" style="display: none;"><i class="fa-solid fa-bug"></i></a>
                </div>
            </div>

            <?
        }

        public static function debug( $data ) {
            $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1 )[0];
            $caller = $backtrace['file'] . ':' . $backtrace['line'];

            self::$data[] = array(
                'data'      => $data,
                'caller'    => $caller,
            );
        }

        public static function debug_p( $data, $clear_previous = false ) {
            $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1 )[0];
            $caller = 'PERSISTENT: ' . $backtrace['file'] . ':' . $backtrace['line'];

            if ( ! isset( $_SESSION['_debugger_data'] ) || $clear_previous ) {
                $_SESSION['_debugger_data'] = array();
            }

            $new_data = array(
                'data'      => $data,
                'caller'    => $caller,
            );

            if ( ! in_array( $new_data, $_SESSION['_debugger_data'] ) ) {
                $_SESSION['_debugger_data'][] = $new_data;
            }

        }

        public static function clear_persistent_data() {
            if ( isset( $_SESSION['_debugger_data'] ) ) {
                unset( $_SESSION['_debugger_data'] );
            }
        }

    }

    Debugger::init();

}