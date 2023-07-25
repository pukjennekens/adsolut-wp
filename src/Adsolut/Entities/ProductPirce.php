<?php
    namespace PixelOne\Connectors\Adsolut\Entities;

    use PixelOne\Connectors\Adsolut\Model;
    use PixelOne\Connectors\Adsolut\Actions\FindAll;

    /**
     * Product pirce class
     * 
     * @property string $productId
     * @property string $unitId
     * @property string $currencyId
     * @property string $priceCategoryId
     * @property string $customerId
     * @property string $customerDiscountGroupId
     * @property string $salesPriceExclVat
     * @property string $salesPriceInclVat
     * @property string $startDate
     * @property string $endDate
     * @property string $minQuantity
     * @property string $created
     * @property string $lastModified
     * @property string $id
     */
    class ProductPirce extends Model
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
        protected $endpoint = 'ProductPrices';

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
            'unitId',
            'productId',
            'currencyId',
            'priceCategoryId',
            'customerId',
            'customerDiscountGroupId',
            'salesPriceExclVat',
            'salesPriceInclVat',
            'startDate',
            'endDate',
            'minQuantity',
            'created',
            'lastModified',
            'id',
        );
    }