<?php
    namespace PixelOne\Connectors\Adsolut;

    /**
     * Model class
     */
    abstract class Model
    {
        const NESTING_TYPE_ARRAY_OF_OBJECTS = 'array_of_objects';
        const NESTING_TYPE_NESTED_OBJECTS   = 'nested_objects';

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
         * @var array $single_nested_entities The model single nested entities
         */
        protected $single_nested_entities = array();

        /**
         * Array containing the name of the attribute that contains nested objects as key and an array with the entity name
         * and json representation type.
         * 
         * JSON representation of an array of objects (NESTING_TYPE_ARRAY_OF_OBJECTS) [ {}, {}, {} ]
         * JSON representation of nested objects (NESTING_TYPE_NESTED_OBJECTS) { "0": {}, "1": {}, "2": {} }
         * 
         * @var array $multiple_nested_entities The model multiple nested entities
         */
        protected $multiple_nested_entities = array();

        /**
         * Model constructor
         * @param \PixelOne\Connectors\Adsolut\Connection $connection
         * @param array $attributes
         * @return void
         */
        public function __construct( Connection $connection, array $attributes = array() )
        {
            $this->connection = $connection;
            $this->fill( $attributes, true );
        }

        /**
         * Get the class as a plain object
         * @return object
         */
        public function to_object()
        {
            return (object) $this->attributes;
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

        /**
         * Create a collection of models from plain result data
         * @param array $result
         * @return array
         */
        public function collection_from_result( $result )
        {
            if( (bool) count( array_filter( array_keys( $result ), 'is_string' ) ) )
                $result = [$result];

            $collection = array();
            foreach( $result as $_result ) {
                $collection[] = $this->make_from_response( $_result );
            }

            return $collection;
        }

        /**
         * Create a new object with the response from the API
         * @param array $response
         * @return static
         */
        public function make_from_response( $response )
        {
            $entity = new static( $this->connection );
            $entity->self_from_response( $response );

            return $entity;
        }

        /**
         * Recreate this object with the response from the API
         * 
         * @param array $response
         * @return $this
         */
        public function self_from_response( $response )
        {
            $this->fill( $response, true );

            foreach( $this->get_single_nested_entities() as $key => $value ) {
                if( isset( $response[ $key ] ) ) {
                    $entity_name = $value;
                    $this->$key = new $entity_name( $this->connection, $response[ $key ] );
                }
            }

            foreach( $this->get_multiple_nested_entities() as $key => $value ) {
                if( isset( $response[ $key ] ) ) {
                    $entity_name = $value['entity'];
                    $initiated_entity = new $entity_name( $this->connection );
                    $this->$key = $initiated_entity->collection_from_result( $response[ $key ] );
                }
            }
        }

        /**
         * Get the single nested entities
         * @return array
         */
        public function get_single_nested_entities()
        {
            return $this->single_nested_entities;
        }

        /**
         * Get the multiple nested entities
         * @return array
         */
        public function get_multiple_nested_entities()
        {
            return $this->multiple_nested_entities;
        }

        /**
         * Make a var_dump and print_r look better
         * @return array
         */
        public function __debugInfo()
        {
            $result = array();

            foreach( $this->fillable as $attribute ) {
                $result[ $attribute ] = $this->$attribute;
            }

            return $result;
        }

        /**
         * Determine if an attribute exists on the model.
         * @param string $key The attribute key
         * @return bool
         */
        public function __isset( $key )
        {
            return isset( $this->attributes[ $key ] ) && !is_null( $this->attributes[ $key ] );
        }

        /**
         * When the object is converted to an array
         * @return array
         */
        public function to_array()
        {
            $result = array();

            foreach( $this->fillable as $attribute ) {
                $result[ $attribute ] = $this->$attribute;
            }

            return $result;
        }
    }