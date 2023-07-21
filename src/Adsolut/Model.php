<?php
    namespace PixelOne\Connectors\Adsolut;

    /**
     * Model class
     */
    abstract class Model
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection
         */
        protected $connection;

        /**
         * @var array $attributes The model attributes
         */
        protected $attributes = array();

        /**
         * @var array $fillable The model fillable attributes
         */
        protected $fillable = array();

        /**
         * @var string $source The API source
         */
        protected $source;

        /**
         * @var string $version The API version
         */
        protected $version;

        /**
         * @var string $endpoint The model endpoint
         */
        protected $endpoint;

        /**
         * @var bool $without_administration_id Should the model make requests without administration ids?
         */
        protected $without_administration_id = false;

        /**
         * @var string $primary_key The model primary key
         */
        protected $primary_key = 'id';

        /**
         * @var bool $initialized The model initialized
         */
        protected $initialized = false;

        /**
         * Model constructor
         * @param \PixelOne\Connectors\Adsolut\Connection $connection
         * @param array $attributes
         * @return void
         */
        public function __construct( Connection $connection, array $attributes = array() )
        {
            $this->connection = $connection;
            $this->fill( $attributes );
        }

        /**
         * First initialize the model
         * @return void
         */
        protected function enable_first_initialize()
        {
            if ( $this->initialized )
                return;

            $this->initialized = true;
        }

        /**
         * Disable first initialize the model
         * @return void
         */
        protected function disable_first_initialize()
        {
            $this->initialized = true;
        }

        /**
         * Fill the model with an array of attributes
         * @param array $attributes The model attributes
         * @param  bool  $first_initialize If true, the model will be initialized for the first time
         * @return void
         */
        public function fill( $attributes, $first_initialize = false )
        {
            if( $first_initialize )
                $this->enable_first_initialize();

            foreach ( $attributes as $key => $value ) {
                if( $this->is_fillable( $key ) )
                    $this->set_attribute( $key, $value );
            }

            if( $first_initialize )
                $this->disable_first_initialize();
        }

        /**
         * Check if an attribute is fillable
         * @param string $key The attribute key
         * @return bool
         */
        public function is_fillable( $key )
        {
            return in_array( $key, $this->fillable, true );
        }

        /**
         * Set a given attribute on the model.
         * @param string $key The attribute key
         * @param mixed $value The attribute value
         * @return void
         */
        public function set_attribute( $key, $value )
        {
            $this->attributes[ $key ] = $value;
        }

        /**
         * Get an attribute from the model.
         * @param string $key The attribute key
         * @return mixed
         */
        public function __get( $key )
        {
            if( isset( $this->attributes[ $key ] ) )
                return $this->attributes[ $key ];

            return null;
        }

        /**
         * Set an attribute on the model.
         * @param string $key The attribute key
         * @param mixed $value The attribute value
         * @return void
         */
        public function __set( $key, $value )
        {
            if( $this->is_fillable( $key ) )
                $this->set_attribute( $key, $value );
        }

        /**
         * Get the connection
         * @return \PixelOne\Connectors\Adsolut\Connection
         */
        public function connection()
        {
            return $this->connection;
        }

        /**
         * Get the API source
         * @return string
         */
        public function source()
        {
            return $this->source;
        }

        /**
         * Get the API version
         * @return string
         */
        public function version()
        {
            return $this->version;
        }

        /**
         * Get the model endpoint
         * @return string
         */
        public function endpoint()
        {
            return $this->endpoint;
        }

        /**
         * Get if to request without administration id
         * @return bool
         */
        public function without_administration_id()
        {
            return $this->without_administration_id;
        }
    }