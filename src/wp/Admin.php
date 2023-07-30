<?php
    namespace PixelOne\Plugins\Adsolut;
    defined( 'ABSPATH' ) || die;

    use PixelOne\Plugins\Adsolut\Exceptions\AdminException;

    /**
     * The admin class for the Adsolut WordPress integration
     * This class has all the settings fields, ajax handlers and admin pages inside
     */
    class Admin
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection The Adsolut connection
         */
        private static $connection;

        /**
         * Initialize the admin class,
         * @param \PixelOne\Connectors\Adsolut\Connection $connection The connection
         * @throws PixelOne\Plugins\Adsolut\AdminException If the connection is not configured
         * @return void
         */
        public static function init( $connection )
        {
            if( ! $connection || ! $connection instanceof \PixelOne\Connectors\Adsolut\Connection )
                throw new AdminException( __( 'The Adsolut connection is not configured', 'adsolut' ) );

            self::$connection = $connection;

            // Add the menu item to the admin menu
            add_action( 'admin_menu', array( self::class, 'add_menu' ) );

            // Register the settings
            add_action( 'admin_init', array( self::class, 'register_settings' ) );

            // Add an admin notice if the plugin is not configured
            add_action( 'admin_notices', array( self::class, 'admin_notice' ) );

            // Admin page actions
            add_action( 'admin_post_adsolut_test_mode', array( self::class, 'admin_post_test_mode' ) );

            // Create a admin page for the oauth callback url
            add_action( 'admin_post_adsolut_oauth_callback', array( self::class, 'admin_post_oauth_callback' ) );

            // Create an admin page for the logout url
            add_action( 'admin_post_adsolut_logout', array( self::class, 'admin_post_logout' ) );
        }

        /**
         * Add the menu item to the admin menu
         * @return void
         */
        public static function add_menu()
        {
            add_menu_page(
                __( 'Adsolut', 'adsolut' ),
                __( 'Adsolut', 'adsolut' ),
                'manage_options',
                'adsolut',
                array( self::class, 'render_page' ),
                'dashicons-database-import',
                100
            );
        }

        /**
         * Render the admin page
         * @return void
         */
        public static function render_page()
        {            
            Utils::render_template( 'admin/settings', array(
                'redirect_uri'       => self::get_redirect_uri(),
                'logout_uri'         => self::get_logout_uri(),
                'is_configured'      => self::is_configured(),
                'errors'             => self::get_errors(),
                'is_logged_in'       => self::is_logged_in(),
                'auth_url'           => self::get_auth_url(),
                'test_mode'          => self::get_test_mode(),
                'access_token'       => self::get_access_token(),
                'refresh_token'      => self::get_refresh_token(),
                'expires_at'         => self::get_expires_at(),
                'authorization_code' => self::get_authorization_code(),
            ) );
        }

        /**
         * Register the settings
         * @return void
         */
        public static function register_settings()
        {
            register_setting( 'adsolut', 'adsolut_settings' );
            register_setting( 'adsolut', 'adsolut_test_mode' );
            register_setting( 'adsolut', 'adsolut_authorization_code' );
            register_setting( 'adsolut', 'adsolut_access_token' );
            register_setting( 'adsolut', 'adsolut_refresh_token' );
            register_setting( 'adsolut', 'adsolut_token_expires_at' );

            add_settings_section(
                'adsolut_settings_section',
                __( 'Algemene instellingen', 'adsolut' ),
                array( self::class, 'render_settings_section' ),
                'adsolut'
            );

            // Client ID
            add_settings_field(
                'adsolut_client_id',
                __( 'Client ID', 'adsolut' ),
                array( self::class, 'render_client_id_field' ),
                'adsolut',
                'adsolut_settings_section'
            );

            // Client Secret
            add_settings_field(
                'adsolut_client_secret',
                __( 'Client Secret', 'adsolut' ),
                array( self::class, 'render_client_secret_field' ),
                'adsolut',
                'adsolut_settings_section'
            );

            if( self::get_test_mode() ) {
                // Redirect URI
                add_settings_field(
                    'adsolut_redirect_uri',
                    __( 'Redirect URI voor development', 'adsolut' ),
                    array( self::class, 'render_redirect_uri_field' ),
                    'adsolut',
                    'adsolut_settings_section'
                );
            }

            if( self::is_logged_in() ) {
                add_settings_field(
                    'adsolut_administration_id',
                    __( 'Administratie ID', 'adsolut' ),
                    array( self::class, 'render_administration_id_field' ),
                    'adsolut',
                    'adsolut_settings_section'
                );

                if( self::get_administration_id() ) {
                    add_settings_field(
                        'adsolut_catalogues',
                        __( 'Catalogi', 'adsolut' ),
                        array( self::class, 'render_catalogues_field' ),
                        'adsolut',
                        'adsolut_settings_section'
                    );

                    add_settings_field(
                        'adsolut_price_category_id',
                        __( 'Prijs categorie code', 'adsolut' ),
                        array( self::class, 'render_price_category_id_field' ),
                        'adsolut',
                        'adsolut_settings_section'
                    );
                }
            }

            // All other settings, render a input hidden field to prevent them from being overwritten
            $settings = get_option( 'adsolut_settings', array() );
            foreach( $settings as $key => $value )
            {
                if( in_array( $key, array( 'client_id', 'client_secret', 'administration_id', 'redirect_uri', 'catalogues', 'price_category_id' ) ) )
                    continue;

                add_settings_field(
                    'adsolut_' . $key,
                    '',
                    array( self::class, 'render_hidden_field' ),
                    'adsolut',
                    'adsolut_settings_section',
                    array(
                        'key'   => $key,
                        'value' => $value,
                    )
                );
            }
        }

        /**
         * Render a hidden field
         * @param array $args
         * @return void
         */
        public static function render_hidden_field( $args )
        {
            echo '<input type="hidden" name="adsolut_settings[' . $args['key'] . ']" value="' . esc_attr( $args['value'] ) . '" />';
        }

        /**
         * Render the settings section
         * @return void
         */
        public static function render_settings_section()
        {
            echo '<p>' . __( 'Vul hieronder de gegevens in die je van Adsolut hebt ontvangen.', 'adsolut' ) . '</p>';
        }

        /**
         * Render the client ID field
         * @return void
         */
        public static function render_client_id_field()
        {
            $settings = get_option( 'adsolut_settings' );
            $client_id = isset( $settings['client_id'] ) ? $settings['client_id'] : '';

            echo '<input type="text" name="adsolut_settings[client_id]" value="' . esc_attr( $client_id ) . '" class="regular-text" />';
        }

        /**
         * Render the client secret field
         * @return void
         */
        public static function render_client_secret_field()
        {
            $settings = get_option( 'adsolut_settings' );
            $client_secret = isset( $settings['client_secret'] ) ? $settings['client_secret'] : '';

            echo '<input type="password" name="adsolut_settings[client_secret]" value="' . esc_attr( $client_secret ) . '" class="regular-text" />';
        }

        /**
         * Render the redirect URI field
         * @return void
         */
        public static function render_redirect_uri_field()
        {
            $settings = get_option( 'adsolut_settings' );
            $redirect_uri = isset( $settings['redirect_uri'] ) ? $settings['redirect_uri'] : '';

            echo '<input type="text" name="adsolut_settings[redirect_uri]" value="' . esc_attr( $redirect_uri ) . '" class="regular-text" />';
        }

        /**
         * Render the administration ID field
         * @return void
         */
        public static function render_administration_id_field()
        {
            // Check if the administrations are in the cache
            if( false === ( $administrations = get_transient( 'adsolut_administrations' ) ) )
            {
                // Get the administrations
                $administrations = new \PixelOne\Connectors\Adsolut\Entities\Administration( self::$connection );
                $administrations = $administrations->get_all();

                // Make the administrations in the array plain objects instead of \PixelOne\Connectors\Adsolut\Entities\Administration objects
                $administrations = array_map( function( $administration ) {
                    return $administration->to_object();
                }, $administrations );

                // Cache the administrations for 1 hour
                set_transient( 'adsolut_administrations', $administrations, HOUR_IN_SECONDS );
            }

            $settings = get_option( 'adsolut_settings' );
            $administration_id = isset( $settings['administration_id'] ) ? $settings['administration_id'] : '';

            echo '<select name="adsolut_settings[administration_id]">';
            echo '<option value="">' . __( 'Selecteer een administratie', 'adsolut' ) . '</option>';
            foreach( $administrations as $administration )
            {
                echo '<option value="' . esc_attr( $administration->id ) . '" ' . selected( $administration_id, $administration->id, false ) . '>' . esc_html( $administration->name ) . '</option>';
            }
            echo '</select>';
        }

        /**
         * Render the catalogues field
         * @return void
         */
        public static function render_catalogues_field()
        {
            $catalogues = new \PixelOne\Connectors\Adsolut\Entities\Catalogue( self::$connection );
            $catalogues = $catalogues->get_all();

            $settings = get_option( 'adsolut_settings' );
            $catalogues_selected = isset( $settings['catalogues'] ) ? $settings['catalogues'] : array();
            
            echo '<ul>';
            foreach( $catalogues as $catalogue )
            {
                echo '<li>';
                echo '<label>';
                echo '<input type="checkbox" name="adsolut_settings[catalogues][]" value="' . esc_attr( $catalogue->code ) . '" ' . checked( in_array( $catalogue->code, $catalogues_selected ), true, false ) . ' />';
                echo esc_html( $catalogue->description[0]['value'] );
                echo '</label>';
                echo '</li>';
            }
        }

        /**
         * Render the price category code field
         * @return void
         */
        public static function render_price_category_id_field()
        {
            $price_categories = get_adsolut_price_categories();
            
            $settings = get_option( 'adsolut_settings' );
            $price_category_id = isset( $settings['price_category_id'] ) ? $settings['price_category_id'] : '';

            if( ! empty( $price_categories ) ) {
                echo '<select name="adsolut_settings[price_category_id]">';
                echo '<option value="">' . __( 'Selecteer een prijscategorie', 'adsolut' ) . '</option>';
                foreach( $price_categories as $price_category ) {
                    echo '<option value="' . esc_attr( $price_category->adsolut_id ) . '" ' . selected( $price_category_id, $price_category->adsolut_id, false ) . '>' . esc_html( $price_category->code ) . ' (' . esc_html( $price_category->description ) . ')' . '</option>';
                }
                echo '</select>';
            } else {
                echo '<p>' . __( 'Er zijn geen prijscategorieën gevonden. Voer eerst de synchronisatie uit', 'adsolut' ) . '</p>';
            }
        }
        
        /**
         * Get the client ID
         * @return string
         */
        public static function get_client_id()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['client_id'] ) ? $settings['client_id'] : '';
        }

        /**
         * Get the client secret
         * @return string
         */
        public static function get_client_secret()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['client_secret'] ) ? $settings['client_secret'] : '';
        }

        /**
         * Check if the plugin is configured
         * @return boolean
         */
        public static function is_configured()
        {
            $settings = get_option( 'adsolut_settings' );
            return ! empty( $settings['client_id'] ) && ! empty( $settings['client_secret'] );
        }

        /**
         * Set the access token
         * @param string $access_token
         * @return void
         */
        public static function set_access_token( $access_token )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['access_token'] = $access_token;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the access token
         * @return string
         */
        public static function get_access_token()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['access_token'] ) ? $settings['access_token'] : '';
        }

        /**
         * Set the refresh token
         * @param string $refresh_token
         * @return void
         */
        public static function set_refresh_token( $refresh_token )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['refresh_token'] = $refresh_token;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the refresh token
         * @return string
         */
        public static function get_refresh_token()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['refresh_token'] ) ? $settings['refresh_token'] : '';
        }

        /**
         * Set the expires at
         * @param string $expires_in
         * @return void
         */
        public static function set_expires_at( $expires_at )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['token_expires_at'] = $expires_at;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the expires at
         * @return int
         */
        public static function get_expires_at()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['token_expires_at'] ) ? intval( $settings['token_expires_at'] ) : 0;
        }

        /**
         * Set the autorization code
         * @param string $authorization_code
         * @return void
         */
        public static function set_authorization_code( $authorization_code )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['authorization_code'] = $authorization_code;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the authorization code
         * @return string
         */
        public static function get_authorization_code()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['authorization_code'] ) ? $settings['authorization_code'] : '';
        }

        /**
         * Set the administration ID
         * @param string $administration_id
         * @return void
         */
        public static function set_administration_id( $administration_id )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['administration_id'] = $administration_id;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the administration ID
         * @return string
         */
        public static function get_administration_id()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['administration_id'] ) ? $settings['administration_id'] : '';
        }

        /**
         * Check if the user is logged in to Adsolut
         * @return boolean
         */
        public static function is_logged_in()
        {
            return ! empty( self::get_authorization_code() ) && ! empty( self::get_access_token() );
        }

        /**
         * Set the test mode
         * @param boolean $test_mode
         * @return void
         */
        public static function set_test_mode( $test_mode )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['test_mode'] = $test_mode;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the test mode
         * @return boolean
         */
        public static function get_test_mode()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['test_mode'] ) ? $settings['test_mode'] : false;
        }

        /**
         * Set the price category code
         * @param string $price_category_id
         * @return void
         */
        public static function set_price_category_id( $price_category_id )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['price_category_id'] = $price_category_id;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get the price category code
         * @return string
         */
        public static function get_price_category_id()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['price_category_id'] ) ? $settings['price_category_id'] : '';
        }

        /**
         * Set catalogues
         * @param array $catalogues
         * @return void
         */
        public static function set_catalogues( $catalogues )
        {
            $settings = get_option( 'adsolut_settings' );
            $settings['catalogues'] = $catalogues;
            update_option( 'adsolut_settings', $settings );
        }

        /**
         * Get catalogues
         * @return array
         */
        public static function get_catalogues()
        {
            $settings = get_option( 'adsolut_settings' );
            return isset( $settings['catalogues'] ) ? $settings['catalogues'] : array();
        }

        /**
         * Add an admin notice if the plugin is not configured
         * @return void
         */
        public static function admin_notice()
        {
            if( ! self::is_configured() ) {
                ?>
                <div class="notice notice-error">
                    <p><?php _e( 'De Adsolut plugin is nog niet geconfigureerd. Ga naar de <a href="' . admin_url( 'admin.php?page=adsolut' ) . '">instellingen</a> om de plugin te configureren.', 'adsolut' ); ?></p>
                </div>
                <?php
            }
        }

        /**
         * Get the redirect URI
         * @return string
         */
        public static function get_redirect_uri()
        {
            $test_redirect_uri = isset( get_option( 'adsolut_settings' )['redirect_uri'] ) ? get_option( 'adsolut_settings' )['redirect_uri'] : '';
            return ( self::get_test_mode() && ! empty( $test_redirect_uri ) ) ? $test_redirect_uri : admin_url( 'admin-post.php?action=adsolut_oauth_callback' );
        }

        /**
         * Get the logout URI
         * @return string
         */
        public static function get_logout_uri()
        {
            // Return the admin action URL
            return admin_url( 'admin-post.php?action=adsolut_logout' );
        }

        /**
         * Get the auth URL
         * @return string
         */
        public static function get_auth_url()
        {
            return self::is_configured() ? self::$connection->get_auth_url() : '';
        }

        /**
         * Get errors in the Adsolut plugin
         * @return array
         */
        public static function get_errors()
        {
            $errors = array();

            if( self::get_test_mode() )
                $errors[] = __( 'De Adsolut plugin staat in test modus. De plugin zal geen data naar Adsolut sturen.', 'adsolut' );

            return $errors;
        }

        /**
         * Test mode action
         * @return void
         */
        public static function admin_post_test_mode()
        {
            if( ! isset( $_POST['action'] ) || $_POST['action'] !== 'adsolut_test_mode' )
                return;

            self::set_test_mode( ! self::get_test_mode() );
            wp_redirect( admin_url( 'admin.php?page=adsolut' ) );
        }

        /**
         * Redirect action
         * @return void
         */
        public static function admin_post_oauth_callback()
        {
            if( ! isset( $_GET['action'] ) || $_GET['action'] !== 'adsolut_oauth_callback' )
                return;

            if( isset( $_GET['code'] ) ) {
                self::set_authorization_code( $_GET['code'] );

                // Show notice and redirect to settings page
                add_settings_error( 'adsolut', 'adsolut', __( 'De plugin is succesvol geconfigureerd.', 'adsolut' ), 'success' );
                wp_redirect( admin_url( 'admin.php?page=adsolut' ) );
            } else {
                wp_redirect( admin_url( 'admin.php?page=adsolut' ) );
            }
        }

        /**
         * Logout action
         * @return void
         */
        public static function admin_post_logout()
        {
            if( ! isset( $_GET['action'] ) || $_GET['action'] !== 'adsolut_logout' )
                return;

            self::set_authorization_code( null );
            self::set_access_token( null );
            self::set_refresh_token( null );
            self::set_administration_id( null );
            self::set_price_category_id( null );
            self::set_catalogues( array() );

            // Show notice and redirect to settings page
            add_settings_error( 'adsolut', 'adsolut', __( 'Je bent succesvol uitgelogd.', 'adsolut' ), 'success' );
            wp_redirect( admin_url( 'admin.php?page=adsolut' ) );
        }
    }