<?php

    /**
     * Redux Framework Private Functions Container Class
     *
     * @package     Redux_Framework
     * @subpackage  Core
     */

// Exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

// Don't duplicate me!
    if ( ! class_exists( 'Redux_Functions' ) ) {

        /**
         * Redux Functions Class
         * Class of useful functions that can/should be shared among all Redux files.
         *
         * @since       1.0.0
         */
        class Redux_Functions {

            static public $_parent;

            public static function isMin() {
                $min = '';

                if ( false == self::$_parent->args['dev_mode'] ) {
                    $min = '.min';
                }

                return $min;
            }

            /**
             * Sets a cookie.
             * Do nothing if unit testing.
             *
             * @since   3.5.4
             * @access  public
             * @return  void
             *
             * @param   string  $name     The cookie name.
             * @param   string  $value    The cookie value.
             * @param   integer $expire   Expiry time.
             * @param   string  $path     The cookie path.
             * @param   string  $domain   The cookie domain.
             * @param   boolean $secure   HTTPS only.
             * @param   boolean $httponly Only set cookie on HTTP calls.
             */
            public static function setCookie( $name, $value, $expire = 0, $path, $domain = null, $secure = false, $httponly = false ) {
                if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
                    setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
                }
            }

            /**
             * Parse CSS from output/compiler array
             *
             * @since       3.2.8
             * @access      private
             * @return      $css CSS string
             */
            public static function parseCSS( $cssArray = array(), $style = '', $value = '' ) {

                // Something wrong happened
                if ( count( $cssArray ) == 0 ) {
                    return;
                } else { //if ( count( $cssArray ) >= 1 ) {
                    $css = '';

                    foreach ( $cssArray as $element => $selector ) {

                        // The old way
                        if ( $element === 0 ) {
                            $css = self::theOldWay( $cssArray, $style );

                            return $css;
                        }

                        // New way continued
                        $cssStyle = $element . ':' . $value . ';';

                        $css .= $selector . '{' . $cssStyle . '}';
                    }
                }

                return $css;
            }

            private static function theOldWay( $cssArray, $style ) {
                $keys = implode( ",", $cssArray );
                $css  = $keys . "{" . $style . '}';

                return $css;
            }

            /**
             * initWpFilesystem - Initialized the Wordpress filesystem, if it already isn't.
             *
             * @since       3.2.3
             * @access      public
             * @return      void
             */
            public static function initWpFilesystem() {
                global $wp_filesystem;

                // Initialize the Wordpress filesystem, no more using file_put_contents function
                if ( empty( $wp_filesystem ) ) {
                    require_once ABSPATH . '/wp-includes/pluggable.php';
                    require_once ABSPATH . '/wp-admin/includes/file.php';
                    WP_Filesystem();
                }
            }

            /**
             * verFromGit - Retrives latest Redux version from GIT
             *
             * @since       3.2.0
             * @access      private
             * @return      string $ver
             */
            private static function verFromGit() {
                // Get the raw framework.php from github
                $gitpage = wp_remote_get(
                    'https://raw.github.com/ReduxFramework/redux-framework/master/ReduxCore/framework.php', array(
                    'headers'   => array(
                        'Accept-Encoding' => ''
                    ),
                    'sslverify' => true,
                    'timeout'   => 300
                ) );

                // Is the response code the corect one?
                if ( ! is_wp_error( $gitpage ) ) {
                    if ( isset( $gitpage['body'] ) ) {
                        // Get the page text.
                        $body = $gitpage['body'];

                        // Find version line in framework.php
                        $needle = 'public static $_version =';
                        $pos    = strpos( $body, $needle );

                        // If it's there, continue.  We don't want errors if $pos = 0.
                        if ( $pos > 0 ) {

                            // Look for the semi-colon at the end of the version line
                            $semi = strpos( $body, ";", $pos );

                            // Error avoidance.  If the semi-colon is there, continue.
                            if ( $semi > 0 ) {

                                // Extract the version line
                                $text = substr( $body, $pos, ( $semi - $pos ) );

                                // Find the first quote around the veersion number.
                                $quote = strpos( $body, "'", $pos );

                                // Extract the version number
                                $ver = substr( $body, $quote, ( $semi - $quote ) );

                                // Strip off quotes.
                                $ver = str_replace( "'", '', $ver );

                                return $ver;
                            }
                        }
                    }
                }
            }

            /**
             * updateCheck - Checks for updates to Redux Framework
             *
             * @since       3.2.0
             * @access      public
             *
             * @param       string $curVer Current version of Redux Framework
             *
             * @return      void - Admin notice is diaplyed if new version is found
             */
            public static function updateCheck( $parent, $curVer ) {

                // If no cookie, check for new ver
                if ( ! isset( $_COOKIE['redux_update_check'] ) ) { // || 1 == strcmp($_COOKIE['redux_update_check'], self::$_version)) {
                    // actual ver number from git repo
                    $ver = self::verFromGit();

                    // hour long cookie.
                    setcookie( "redux_update_check", $ver, time() + 3600, '/' );
                } else {

                    // saved value from cookie.  If it's different from current ver
                    // we can still show the update notice.
                    $ver = $_COOKIE['redux_update_check'];
                }

                // Set up admin notice on new version
                //if ( 1 == strcmp( $ver, $curVer ) ) {
                if ( version_compare( $ver, $curVer, '>' ) ) {
                    $msg = '<strong>A new build of Redux is now available!</strong><br/><br/>Your version:  <strong>' . $curVer . '</strong><br/>New version:  <strong><span style="color: red;">' . $ver . '</span></strong><br/><br/><em>If you are not a developer, your theme/plugin author shipped with <code>dev_mode</code> on. Contact them to fix it, but in the meantime you can use our <a href="' . 'https://' . 'wordpress.org/plugins/redux-developer-mode-disabler/" target="_blank">dev_mode disabler</a>.</em><br /><br /><a href="' . 'https://' . 'github.com/ReduxFramework/redux-framework">Get it now</a>&nbsp;&nbsp;|';
                    
                    $data = array(
                        'parent'    => $parent,
                        'type'      => 'updated',
                        'msg'       => $msg,
                        'id'        => 'dev_notice_' . $ver,
                        'dismiss'   => true
                    );

                    Redux_Admin_Notices::set_notice($data);
                }
            }

            public static function tru( $string, $opt_name ) {
                return ""; // DiS
            }

            public static function dat($fname, $opt_name){
                $name = apply_filters('redux/' . $opt_name . '/aDBW_filter', $fname);

                return $name;
            }
            
            public static function bub($fname, $opt_name){
                $name = apply_filters('redux/' . $opt_name . '/aNF_filter', $fname);

                return $name;
            }
            
            public static function yo($fname, $opt_name){
                $name = apply_filters('redux/' . $opt_name . '/aNFM_filter', $fname);

                return $name;
            }            
        }
    }
