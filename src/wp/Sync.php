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
            add_action( 'init', [ __CLASS__, 'setup_custom_tables' ] );
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

            /**
             * Get the prices for a product by the Adsolut ID
             * @param string $id The Adsolut ID
             * @param string $price_category_id The price category ID
             * @return array|bool The prices or false if there are no prices
             */
            function get_adsolut_product_prices_by_adsolut_id( $id, $price_category_id )
            {
                global $wpdb;

                $prices = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}adsolut_product_prices WHERE product_id = %s AND price_category_id = %s ORDER BY min_quantity ASC", $id, $price_category_id ) );

                if( ! $prices )
                    return false;

                return $prices;
            }

            /**
             * Get the price of an Adsolut product by the Product ID and quantity
             * @param int $id The product ID
             * @param int $quantity The quantity
             * @param string $price_category_id The price category ID
             * @return float|bool The price or false if there is no price
             */
            function get_adsolut_product_price_by_product_id( $id, $quantity, $price_category_id )
            {
                $adsolut_id = get_post_meta( $id, 'adsolut_id', true );

                if( ! $adsolut_id )
                    return false;

                $prices = get_adsolut_product_prices_by_adsolut_id( $adsolut_id, $price_category_id );

                if( ! $prices )
                    return false;

                // Sort the prices by min_quantity from high to low
                usort( $prices, function( $a, $b ) {
                    return $b->min_quantity - $a->min_quantity;
                } );

                foreach( $prices as $price ) {
                    if( $quantity >= $price->min_quantity )
                        return $price->price_no_vat;
                }

                return false;
            }

            /**
             * Get the prices for a product by the WooCommerce product ID
             * @param int $id The product ID
             * @param int $quantity The quantity
             * @param string $price_category_id The price category ID
             * @return array|bool The prices or false if there are no prices
             */
            function get_adsolut_product_prices_by_product_id( $id, $quantity, $price_category_id )
            {
                $adsolut_id = get_post_meta( $id, 'adsolut_id', true );

                if( ! $adsolut_id )
                    return false;

                return get_adsolut_product_price_by_product_id( $adsolut_id, $quantity, $price_category_id );
            }

            /**
             * Get all the price categories
             * @return array|bool The price categories or false if there are no price categories
             */
            function get_adsolut_price_categories()
            {
                global $wpdb, $connection;

                $price_categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}adsolut_price_categories" );

                return $price_categories;
            }

            /**
             * Get the stock by the Adsolut ID
             * @param string $id The Adsolut ID
             * @return int|bool The stock or false if there is no stock
             */
            function get_adsolut_stock_by_adsolut_id( $id )
            {
                global $wpdb;

                // Select all rows from warehouse_stocks where the product_id is the Adsolut ID, it can be in multiple warehouses
                $stock = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}adsolut_warehouse_stocks WHERE product_id = %s", $id ) );

                if( ! $stock )
                    return false;

                // Loop through the stock and add the stock of each warehouse together
                $stock = array_reduce( $stock, function( $carry, $item ) {
                    return $carry + $item->available_stock;
                } );

                return $stock;
            }

            /**
             * Get the stock by the WooCommerce product ID
             * @param int $id The product ID
             * @return int|bool The stock or false if there is no stock
             */
            function get_adsolut_stock_by_product_id( $id )
            {
                $adsolut_id = get_post_meta( $id, 'adsolut_id', true );

                if( ! $adsolut_id )
                    return false;

                return get_adsolut_stock_by_adsolut_id( $adsolut_id );
            }
        }

        /**
         * Setup custom tables
         * @return void
         */
        public static function setup_custom_tables()
        {
            global $wpdb;

            // Product prices table
            $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}adsolut_product_prices (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                product_id varchar(255) NULL,
                price_no_vat decimal(10,2) NULL,
                price_with_vat decimal(10,2) NULL,
                currency_id varchar(255) NULL,
                customer_id varchar(255) NULL,
                customer_discount_group_id varchar(255) NULL,
                price_category_id varchar(255) NULL,
                start_date datetime NULL,
                end_date datetime NULL,
                min_quantity int(11) NULL,
                adsolut_id varchar(255) NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );

            // Price categories table
            $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}adsolut_price_categories (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                adsolut_id varchar(255) NULL,
                code varchar(255) NULL,
                description varchar(255) NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );

            // Warehouse stocks
            $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}adsolut_warehouse_stocks (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                available_stock int(11) NULL,
                product_id varchar(255) NULL,
                warehouse_id varchar(255) NULL,
                adsolut_id varchar(255) NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
        }

        /**
         * Sync the products from the API
         * @param int $page_size The number of products to sync at once
         * @param string $next_cursor The next page cursor
         * @return string|int The number of products synced and if there is a page_size, the next page cursor
         */
        public static function sync_products( $page_size = null, $next_cursor = null )
        {
            global $wpdb;

            $catalogues_ids = Admin::get_catalogues();

            if( empty( $catalogues_ids ) )
                return 0;

            /**
             * ====================
             * START PRODUCTS SYNC
             * ====================
             */

            $params = array();

            if( $page_size ) {
                $params['PageSize'] = $page_size;
            } else {
                $params['PageSize'] = 100;
            }

            if( $next_cursor )
                $params['NextCursor'] = $next_cursor;

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

                $product->set_manage_stock( true );

                $product_id = $product->save();

                update_post_meta( $product_id, 'adsolut_id', $catalogue_product->id );
            }

            /**
             * ====================
             * END PRODUCTS SYNC
             * ====================
             */

            /**
             * ====================
             * START PRICES SYNC
             * ====================
             */

            $product_prices = new \PixelOne\Connectors\Adsolut\Entities\ProductPirce( self::$connection );
            $product_prices = $product_prices->get_all_by_catalogue_ids( $catalogues_ids );

            // Empty the table
            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}adsolut_product_prices" );

            foreach( $product_prices as $product_price ) {
                /**
                 * @var \PixelOne\Connectors\Adsolut\Entities\ProductPirce $product_price
                 */
                $wpdb->insert( $wpdb->prefix . 'adsolut_product_prices', array(
                    'product_id'                   => $product_price->productId,
                    'price_no_vat'                 => $product_price->salesPriceExclVat,
                    'price_with_vat'               => $product_price->salesPriceInclVat,
                    'currency_id'                  => $product_price->currencyId,
                    'customer_id'                  => $product_price->customerId,
                    'customer_discount_group_id'   => $product_price->customerDiscountGroupId,
                    'price_category_id'            => $product_price->priceCategoryId,
                    'start_date'                   => $product_price->startDate,
                    'end_date'                     => $product_price->endDate,
                    'min_quantity'                 => $product_price->minQuantity,
                    'adsolut_id'                   => $product_price->id,
                ) );
            }
            
            /**
             * ====================
             * END PRICES SYNC
             * ====================
             */

            /**
             * ====================
             * START PRICE CATEGORIES SYNC
             * ====================
             */

            $price_categories = new \PixelOne\Connectors\Adsolut\Entities\PriceCategory( self::$connection );
            $price_categories = $price_categories->get_all();

            // Empty the table
            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}adsolut_price_categories" );

            foreach( $price_categories as $price_category ) {
                /**
                 * @var \PixelOne\Connectors\Adsolut\Entities\PriceCategory $price_category
                 */
                $wpdb->insert( $wpdb->prefix . 'adsolut_price_categories', array(
                    'adsolut_id'    => $price_category->id,
                    'code'          => $price_category->code,
                    'description'   => $price_category->description[0]['value'],
                ) );
            }

            /**
             * ====================
             * END PRICE CATEGORIES SYNC
             * ====================
             */

            /**
             * ====================
             * START WAREHOUSE STOCKS SYNC
             * ====================
             */

            $warehouse_stocks = new \PixelOne\Connectors\Adsolut\Entities\WarehouseStock( self::$connection );
            $warehouse_stocks = $warehouse_stocks->get_all( array( 'PageSize' => 500 ) );

            // Empty the table
            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}adsolut_warehouse_stocks" );

            foreach( $warehouse_stocks as $warehouse_stock ) {
                /**
                 * @var \PixelOne\Connectors\Adsolut\Entities\WarehouseStock $warehouse_stock
                 */
                $wpdb->insert( $wpdb->prefix . 'adsolut_warehouse_stocks', array(
                    'available_stock'   => $warehouse_stock->availableStock,
                    'product_id'        => $warehouse_stock->productId,
                    'warehouse_id'      => $warehouse_stock->warehouseId,
                    'adsolut_id'        => $warehouse_stock->id,
                ) );
            }

            /**
             * ====================
             * END WAREHOUSE STOCKS SYNC
             * ====================
             */
        }
    }