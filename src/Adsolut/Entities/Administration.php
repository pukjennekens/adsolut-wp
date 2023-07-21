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
         * @var string $endpoint The model endpoint
         */
        protected $endpoint = 'administrations';

        /**
         * @var string $api The model api
         */
        protected $api = 'adm';

        /**
         * @var string $api_version The model api version
         */
        protected $api_version = 'V1';

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