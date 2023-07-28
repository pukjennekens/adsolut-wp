<?php
    namespace PixelOne\Connectors\Adsolut\Entities;

    use PixelOne\Connectors\Adsolut\Model;
    use PixelOne\Connectors\Adsolut\Actions\FindAll;

    /**
     * WarehouseStock class
     * 
     * @property string availableStock
     * @property string productId
     * @property string warehouseId
     * @property string created
     * @property string lastModified
     * @property string id
     */
    class WarehouseStock extends Model
    {
        use FindAll;

        /**
         * @var string $source The API source
         */
        protected $source = 'erp';

        /**
         * @var string $version The API version
         */
        protected $version = 'v1';

        /**
         * @var string $endpoint The model endpoint
         */
        protected $endpoint = 'WarehouseStocks';

        /**
         * @var bool $without_administration_id Should the model make requests without administration ids?
         */
        protected $without_administration_id = false;

        /**
         * @var string $primary_key The primary key
         */
        protected $primary_key = 'id';

        /**
         * @var array $fillable
         */
        protected $fillable = array(
            'availableStock',
            'productId',
            'warehouseId',
            'created',
            'lastModified',
            'id',
        );
    }