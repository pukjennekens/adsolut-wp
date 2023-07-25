<?php
    namespace PixelOne\Connectors\Adsolut\Entities;

    use PixelOne\Connectors\Adsolut\Model;
    use PixelOne\Connectors\Adsolut\Actions\FindAll;

    /**
     * Administrations class
     * 
     * code
     * @property string $name
     * @property string $description
     * @property string $webName
     * @property string $webDescription
     * @property string $baseUnitId
     * @property string $defaultSalesUnitId
     * @property string $productUnits
     * @property string $brandId
     * @property string $categoryIds
     * @property string $productDiscountGroupId
     * @property string $productDiscountSubGroupId
     * @property string $weight
     * @property string $allowDiscount
     * @property string $stockManagement
     * @property string $vatCodeId
     * @property string $reducedVatCodeId
     * @property string $contributionIds
     * @property string $emptiesContributionId
     * @property string $catalogueIds
     * @property string $catalogueProductSequences
     * @property string $relatedProductIds
     * @property string $variationTypeId
     * @property string $variationOptionId
     * @property string $variationMainProductId
     * @property string $variationPricesFromMainProduct
     * @property string $manufacturerProductNumber
     * @property string $extraData
     * @property string $productExtraInformations
     * @property string $blocked
     * @property string $endOfSeries
     * @property string $orderState
     * @property string $serviceProduct
     * @property string $created
     * @property string $lastModified
     * @property string $id
     */
    class CatalogueProduct extends Model
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
        protected $endpoint = 'CatalogueProducts';

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
            'code',
            'name',
            'description',
            'webName',
            'webDescription',
            'baseUnitId',
            'defaultSalesUnitId',
            'productUnits',
            'brandId',
            'categoryIds',
            'productDiscountGroupId',
            'productDiscountSubGroupId',
            'weight',
            'allowDiscount',
            'stockManagement',
            'vatCodeId',
            'reducedVatCodeId',
            'contributionIds',
            'emptiesContributionId',
            'catalogueIds',
            'catalogueProductSequences',
            'relatedProductIds',
            'variationTypeId',
            'variationOptionId',
            'variationMainProductId',
            'variationPricesFromMainProduct',
            'manufacturerProductNumber',
            'extraData',
            'productExtraInformations',
            'blocked',
            'endOfSeries',
            'orderState',
            'serviceProduct',
            'created',
            'lastModified',
            'id',
        );
    }