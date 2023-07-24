<?php
    namespace PixelOne\Connectors\Adsolut\Entities;

    use PixelOne\Connectors\Adsolut\Model;
    use PixelOne\Connectors\Adsolut\Actions\FindAll;

    /**
     * Administrations class
     * 
     * @property string $id Primary key
     * @property string $companyRegistrationNumber Company registration number
     * @property string $companyRegistrationNumberPrefix Company registration number prefix
     * @property string $name Name
     * @property string $identifier Identifier
     * @property string $displayName Display name
     * @property string $language Language
     * @property string $organizationalUnitId Organizational unit id
     * @property string $administrationType Administration type
     * @property array $capabilities Capabilities
     */
    class Administration extends Model
    {
        use FindAll;

        /**
         * @var string $source The API source
         */
        protected $source = 'adm';

        /**
         * @var string $version The API version
         */
        protected $version = 'v1';

        /**
         * @var string $endpoint The model endpoint
         */
        protected $endpoint = 'administrations';

        /**
         * @var bool $without_administration_id Should the model make requests without administration ids?
         */
        protected $without_administration_id = true;

        /**
         * @var string $primary_key The primary key
         */
        protected $primary_key = 'id';

        /**
         * @var array $fillable
         */
        protected $fillable = array(
            'companyRegistrationNumber',
            'companyRegistrationNumberPrefix',
            'name',
            'id',
            'identifier',
            'displayName',
            'language',
            'organizationalUnitId',
            'administrationType',
            'capabilities',
        );
    }