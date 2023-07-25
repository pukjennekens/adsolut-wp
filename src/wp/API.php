<?php
    namespace PixelOne\Plugins\Adsolut;
    defined( 'ABSPATH' ) || die;

    use PixelOne\Plugins\Adsolut\Exceptions\APIException;
    
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
                register_rest_route( 'adsolut/v1', '/products', [
                    'methods' => 'GET',
                    'callback' => [ __CLASS__, 'get_products' ],
                    'permission_callback' => '__return_true',
                ] );
            }

            // Import products route
            register_rest_route( 'adsolut/v1', '/import-products', [
                'methods' => 'GET',
                'callback' => [ Sync::class, 'sync_products' ],
                'permission_callback' => '__return_true',
            ] );
        }

        /**
         * Get the products from the API.
         * @return \WP_REST_Response
         */
        public static function get_products()
        {
            $catalogues_ids = Admin::get_catalogues();

            if( empty( $catalogues_ids ) )
                return new \WP_REST_Response( array(), 200 );

            $catalogue_products = new \PixelOne\Connectors\Adsolut\Entities\CatalogueProduct( self::$connection );
            $catalogue_products = $catalogue_products->get_all( array( 'CatalogueCodes' => implode( ',', $catalogues_ids ) ) ); 

            $products = array();
            foreach( $catalogue_products as $catalogue_product ) {
                $products[] = $catalogue_product->to_array();
            }

            return new \WP_REST_Response( $products, 200 );
        }
    }