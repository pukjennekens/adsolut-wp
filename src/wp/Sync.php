<?php
    namespace PixelOne\Plugins\Adsolut;
    defined( 'ABSPATH' ) || die;

    use PixelOne\Plugins\Adsolut\Exceptions\APIException;
    use PixelOne\Plugins\Adsolut\Exceptions\PluginException;
    use WC_Product;

    class Sync
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection The Adsolut connection
         */
        private static $connection;

        /**
         * Initialize the API class.
         * @param \PixelOne\Connectors\Adsolut\Connection $connection The connection
         * @throws \PixelOne\Plugins\Adsolut\APIException If the connection is not configured
         * @throws \PixelOne\Plugins\Adsolut\PluginException If the WC_Product class does not exist
         * @return void
         */
        public static function init( $connection )
        {
            if( ! $connection || ! $connection instanceof \PixelOne\Connectors\Adsolut\Connection )
                throw new APIException( __( 'The Adsolut connection is not configured', 'adsolut' ) );

            self::$connection = $connection;

            // Check if the WC_Product class exists
            if( ! class_exists( 'WC_Product' ) )
                throw new PluginException( __( 'The WC_Product class does not exist', 'adsolut' ) );

            add_action( 'init', [ __CLASS__, 'register_functions' ] );
        }

        /**
         * Register the functions
         * @return void
         */
        public static function register_functions()
        {
            /**
             * Get the Adsolut product by the Adsolut ID
             * @param string $id The Adsolut ID
             * @param bool $return_post_object Whether to return the post object or the product ID
             * @return \WP_Post|int
             */
            function get_adsolut_product_by_id( $id, $return_post_object = false )
            {
                $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 1,
                    'post_status'    => 'any',
                    'fields'         => 'ids',
                    'meta_query'     => array(
                        array(
                            'key'     => 'adsolut_id',
                            'value'   => $id,
                            'compare' => '='
                        )
                    ),
                );

                $products = get_posts( $args );

                if( ! $products )
                    return false;

                $product = wc_get_product( $products[0] );

                if( $return_post_object )
                    return $product;

                return $product->get_id();
            }
        }

        /**
         * Sync the products from the API
         * @param int $page_size The number of products to sync at once
         * @param string $next_cursor The next page cursor
         * @return string|int The number of products synced and if there is a page_size, the next page cursor
         */
        public static function sync_products( $page_size = null, $next_cursor = null )
        {
            $params = array();

            if( $page_size ) {
                $params['PageSize'] = $page_size;
            } else {
                $params['PageSize'] = 100;
            }

            if( $next_cursor )
                $params['NextCursor'] = $next_cursor;

            $catalogues_ids = Admin::get_catalogues();

            if( empty( $catalogues_ids ) )
                return 0;

            $params['CatalogueCodes'] = implode( ',', $catalogues_ids );
                
            $response           = self::$connection->request( 'GET', 'erp', 'V1', 'CatalogueProducts', false, array(), $params );
            $catalogue_products = $response['data'];

            if( ! $catalogue_products )
                return 0;

            foreach( $catalogue_products as $catalogue_product ) {
                $catalogue_product = ( new \PixelOne\Connectors\Adsolut\Entities\CatalogueProduct( self::$connection ) )->make_from_response( $catalogue_product );

                /**
                 * @var WC_Product $product
                 */
                if( ! $product = get_adsolut_product_by_id( $catalogue_product->id, true ) )
                    $product = new WC_Product();

                $product->set_name( $catalogue_product->name[0]['value'] );

                $product_id = $product->save();

                update_post_meta( $product_id, 'adsolut_id', $catalogue_product->id );
            }
        }
    }