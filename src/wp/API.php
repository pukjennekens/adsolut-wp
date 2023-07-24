<?php
    namespace PixelOne\Plugins\Adsolut;

use PixelOne\Plugins\Adsolut\Exceptions\APIException;

    defined( 'ABSPATH' ) || die;

    class API
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection The Adsolut connection
         */
        private static $connection;

        /**
         * Initialize the API class.
         * @param \PixelOne\Connectors\Adsolut\Connection $connection The connection
         * @throws PixelOne\Plugins\Adsolut\APIException If the connection is not configured
         * @return void
         */
        public static function init( $connection )
        {
            if( ! $connection || ! $connection instanceof \PixelOne\Connectors\Adsolut\Connection )
                throw new APIException( __( 'The Adsolut connection is not configured', 'adsolut' ) );

            self::$connection = $connection;

            // Register the API routes
            add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
        }

        /**
         * Check if the app is in testing mode
         * @return bool
         */
        public static function is_testing()
        {
            return true;
        }

        /**
         * Register the API routes.
         * @return void
         */
        public static function register_routes()
        {
            // Testing route
            if( self::is_testing() ) {
                register_rest_route( 'adsolut/v1', '/test', [
                    'methods' => 'GET',
                    'callback' => [ __CLASS__, 'test' ],
                    'permission_callback' => '__return_true',
                ] );
            }
        }

        /**
         * Test the API connection.
         * @return \WP_REST_Response
         */
        public static function test()
        {
            
        }
    }