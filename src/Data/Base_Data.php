<?php

/**
 * Base class for data models.
 *
 * @template TData of array<string,mixed>
 */
abstract class SC_Data {
    /**
     * Object type.
     *
     * @var string
     */
    protected string $object_type;

    /**
     * Data model ID
     *
     * @var string
     */
    protected string $id = '';

    /**
     * Data model data
     *
     * @var TData
     */
    protected array $data;

    /**
     * Default data model data
     *
     * @var TData
     */
    protected array $default_data;

    /**
     * Changes made to the data model.
     *
     * @var array<string,mixed>
     */
    protected array $changes = array();

    /**
     * Whether the model is persisted.
     *
     * @var bool
     */
    protected bool $object_read = false;

    /**
     * Constructor.
     *
     * @param  null|string|array<string,mixed> $data Data to load.
     */
    public function __construct( null|string|array $data = null ) {
        $this->load_data( $data );
    }

    /**
     * Universal prop getter / setter
     *
     * @param  string       $name Method name.
     * @param  array<mixed> $args Method arguments.
     * @return mixed        Void or prop value.
     *
     * @throws \BadMethodCallException If prop does not exist.
     */
    public function __call( string $name, array $args ): mixed {
        [ $name, $type, $prop ] = $this->parse_method_name( $name, $args );

        return 'get' === $type
            ? $this->get_prop( $prop )
            : $this->set_prop( $prop, $args[0] );
    }

    /**
     * Get the default data.
     *
     * @return TData
     */
    abstract protected function get_default_data(): array;

    /**
     * Get the object type.
     *
     * @return string
     */
    public function get_object_type(): string {
        return $this->object_type;
    }

    /**
     * Get the options ID
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Set the ID
     *
     * @param  string $id Options ID.
     * @return static
     */
    public function set_id( string $id ): static {
        $this->id = $id;

        return $this;
    }

    /**
     * Set all props to default values.
     *
     * @return static
     */
    public function set_defaults(): static {
        $this->data    = $this->get_default_data();
        $this->changes = array();

        return $this->set_object_read( false );
    }

    /**
     * Set object read property.
     *
     * @param bool $read Should read?.
     * @return static
     */
    public function set_object_read( bool $read = true ): static {
        $this->object_read = $read;

        return $this;
    }

    /**
     * Set multiple props.
     *
     * @param  array<string,mixed> $props Props to set.
     * @return \WP_Error|true
     */
    public function set_props( array $props ): \WP_Error|bool {
        $errors = null;
        foreach ( $props as $prop => $value ) {
            try {
                $this->{"set_{$prop}"}( $value );

            } catch ( \Exception $e ) {
                $errors ??= new \WP_Error();

                $errors->add( $e->getCode(), $e->getMessage(), array( 'prop' => $prop ) );
            }
        }

        return $errors ?? true;
    }

    /**
     * Get object read property.
     *
     * @since  3.0.0
     * @return boolean
     */
    public function get_object_read() {
        return (bool) $this->object_read;
    }

    /**
     * Get data.
     *
     * @return TData
     */
    public function get_data(): array {
        return \array_merge( array( 'id' => $this->get_id() ), $this->data );
    }

    /**
     * Apply changes to the model.
     *
     * @return static
     */
    public function apply_changes(): static {
        $this->data    = \array_replace_recursive( $this->data, $this->changes );
        $this->changes = array();

        return $this;
    }

    /**
     * Save the model.
     *
     * @param  ?string $id ID to save. Optional.
     * @return static
     *
     * @throws \Exception If the ID is not set.
     */
    public function save( ?string $id = null ): static {
        $id ??= $this->get_id();

        if ( ! $id ) {
            throw new \Exception( 'ID is required to save the model.' );
        }

        return $this
            ->apply_changes()
            ->set_id( $id )
            ->set_option( $this->get_data() );
    }

    /**
     * Delete the model.
     *
     * @return static
     */
    public function delete(): static {
        return $this
            ->set_defaults()
            ->delete_option();
    }

    /**
     * Load data into the model.
     *
     * @param  null|string|array<string,mixed> $data Data to load.
     */
    protected function load_data( null|string|array $data ): void {
        $this->data         = $this->get_default_data();
        $this->default_data = $this->data;

        if ( $data && \is_string( $data ) ) {
            $this->set_id( $data );
            $data = $this->get_option();
        }

        if ( $data && \is_array( $data ) ) {
            $this->set_props( $data );
        }

        $this->set_object_read( true );
    }

    /**
     * Get a property value.
     *
     * @param  string $prop Property name.
     * @return mixed
     */
    protected function get_prop( string $prop ): mixed {
        if ( ! \array_key_exists( $prop, $this->data ) ) {
            return null;
        }

        return \array_key_exists( $prop, $this->changes )
            ? $this->changes[ $prop ]
            : $this->data[ $prop ];
    }

    /**
     * Set a property value.
     *
     * @param  string $prop  Property name.
     * @param  mixed  $value Property value.
     * @return static
     */
    protected function set_prop( string $prop, mixed $value ): static {
        if ( ! \array_key_exists( $prop, $this->data ) ) {
            return $this;
        }

        if ( ! $this->object_read ) {
            $this->data[ $prop ] = $value;
            return $this;
        }

        if ( $value !== $this->data[ $prop ] || \array_key_exists( $prop, $this->changes ) ) {
            $this->changes[ $prop ] = $value;
        }

        return $this;
    }

    /**
     * Parses the method name.
     *
     * @param  string             $name Method name.
     * @param  array<mixed,mixed> $args Method arguments.
     * @return array{0: string, 1: string, 2: string}}
     */
    final protected function parse_method_name( string $name, array $args ): array {
        \preg_match( '/^([gs]et)_(.+)$/', $name, $m );

        if ( 3 !== \count( $m ) || ( 'set' === ( $m[1] ?? '' ) && ! isset( $args[0] ) ) ) {
            $this->error( 0, \sprintf( 'BMC: %s, %s', static::class, $name ) );
        }

        return $m;
    }

    /**
     * Throws an error.
     *
     * @param  int    $code    Error code.
     * @param  string $message Error message.
     *
     * @throws \Exception Always.
     */
    protected function error( int $code, string $message ): void {
        throw new \Exception( \esc_html( $message ), \intval( $code ) );
    }

    /**
     * Get option value
     *
     * @return null|TData
     */
    protected function get_option(): ?array {
        return \get_option( $this->get_option_key(), null );
    }

    /**
     * Set option value
     *
     * @param  mixed $value Option value.
     * @return static
     */
    protected function set_option( mixed $value ): static {
        \update_option( $this->get_option_key(), $value );

        return $this;
    }

    /**
     * Delete option
     *
     * @return static
     */
    protected function delete_option(): static {
        \delete_option( $this->get_option_key() );

        return $this;
    }

    /**
     * Get the option key.
     *
     * @return string
     */
    private function get_option_key(): string {
        return "{$this->get_id()}_sc_{$this->get_object_type()}";
    }
}
