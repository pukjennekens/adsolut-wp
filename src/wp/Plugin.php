<?php
    namespace PixelOne\Plugins\Adsolut;

    use PixelOne\Connectors\Adsolut\Exceptions\AdsolutException;

    defined( 'ABSPATH' ) || die;

    /**
     * Plugin class for the Adsolut WordPress integration
     */
    class Plugin
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection The Adsolut connection
         */
        private static $connection;

        /**
         * Initialize the plugin
         * @return void
         */
        public static function init()
        {
            $connection = new \PixelOne\Connectors\Adsolut\Connection();
            Admin::init( $connection );

            if( Admin::is_configured() ) {
                $connection->set_client_id( Admin::get_client_id() );
                $connection->set_client_secret( Admin::get_client_secret() );
                $connection->set_redirect_uri( Admin::get_redirect_uri() );
                $connection->set_test_mode( Admin::get_test_mode() );

                $connection->set_token_update_callback( function( $_connection ) {
                    Admin::set_access_token( $_connection->get_access_token() );
                    Admin::set_refresh_token( $_connection->get_refresh_token() );
                    Admin::set_expires_at( $_connection->get_expires_at() );
                    Admin::set_authorization_code( $_connection->get_authorization_code() );
                } );

                $connection->set_authorization_code( Admin::get_authorization_code() );
                $connection->set_access_token( Admin::get_access_token() );
                $connection->set_refresh_token( Admin::get_refresh_token() );
                $connection->set_token_expires_at( Admin::get_expires_at() );

                if( ! empty( Admin::get_administration_id() ) )
                    $connection->set_administration_id( Admin::get_administration_id() );

                try {
                    $connection->connect();
                } catch( AdsolutException $e ) {
                    Admin::set_authorization_code(null);
                    Admin::set_access_token(null);
                    Admin::set_refresh_token(null);
                    Admin::set_expires_at(null);
                }

                self::$connection = $connection;

                API::init( $connection );
                Sync::init( $connection );
            }
        }
    }