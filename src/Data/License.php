<?php //phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.VariableComment.Missing

/**
 * License model.
 *
 * @extends SC_Data<array{
 *   activation_id: string,
 *   license_id: string,
 *   license_key: string,
 *   registered: bool,
 *   activated: bool,
 *   status: string,
 *   usage_count: int,
 *   usage_limit: int,
 *   created_at: ?int,
 *   revokes_at: ?int,
 *   updated_at: ?int,
 *   validated_at: ?int
 * }>
 *
 * @method string get_activation_id()  Get the activation ID.
 * @method string get_аctivation_url() Get the activation URL.
 * @method string get_license_id()     Get the license ID.
 * @method string get_license_key()    Get the license key.
 * @method bool   get_registered()     Get the registered status.
 * @method bool   get_activated()      Get the activated status.
 * @method bool   get_counted()        Get the counted status.
 * @method string get_status()         Get the status.
 * @method int    get_usage_count()    Get the usage count.
 * @method int    get_usage_limit()    Get the usage limit.
 * @method ?int   get_created_at()     Get the created at timestamp.
 * @method ?int   get_revokes_at()     Get the revokes at timestamp.
 * @method ?int   get_updated_at()     Get the updated at timestamp.
 * @method ?int   get_validated_at()   Get the validated at timestamp.
 *
 * @method static set_activation_id( string $activation_id )   Set the activation ID.
 * @method static set_аctivation_url( string $activation_url ) Set the activation URL.
 * @method static set_activation_ip( string $activation_ip )   Set the activation IP address.
 * @method static set_license_id( string $license_id )         Set the license ID.
 * @method static set_license_key( string $license_key )       Set the license key.
 * @method static set_registered( bool $registered )           Set the registered status.
 * @method static set_activated( bool $activated )             Set the activated status.
 * @method static set_counted( bool $counted )                 Set the counted status.
 * @method static set_status( string $status )                 Set the status.
 * @method static set_usage_count( int $usage_count )          Set the usage count.
 * @method static set_usage_limit( int $usage_limit )          Set the usage limit.
 * @method static set_created_at( int $created_at )            Set the created at timestamp.
 * @method static set_revokes_at( int $revokes_at )            Set the revokes at timestamp.
 * @method static set_updated_at( int $updated_at )            Set the updated at timestamp.
 * @method static set_validated_at( int $validated_at )        Set the validated at timestamp.
 */
class SC_License extends SC_Data {
    protected string $object_type = 'license';

    /**
     * Default license key.
     *
     * @var string
     */
    private string $def_key = '00000000-0000-0000-0000-000000000000';

    /**
     * License fingerprint.
     *
     * @var string
     */
    private string $fingerprint;

    /**
     * Website name.
     *
     * @var string
     */
    private string $name;

    /**
     * Create a license object from data.
     *
     * @param  array{id?: string, license_key?: string} $post Post data.
     * @return self
     */
    public static function from_data( array $post ): self {
        $id  = $post['id'] ?? null;
        $key = $post['license_key'] ?? '';

        return ( new self( $id ) )->set_license_key( $key );
    }

    /**
     * Get the activation IP address.
     *
     * @return string
     */
    public function get_activation_ip(): string {
        return $this->get_prop( 'activation_ip' )
        ??
        $this->set_prop(
            'activation_ip',
            gethostbyname( wp_parse_url( $this->get_fingerprint(), PHP_URL_HOST ) ),
        )->get_prop( 'activation_ip' );
    }

    /**
     * Get the activation data.
     *
     * @return array{
     *   fingerprint: string,
     *   name: string,
     *   license: string,
     * }
     */
    public function get_activation(): array {
        return array(
            'fingerprint' => $this->get_fingerprint(),
            'name'        => $this->get_name(),
            'license'     => $this->get_license_id(),
        );
    }

    /**
     * Get the license fingerprint.
     *
     * @return string
     */
    public function get_fingerprint(): string {
        return $this->fingerprint ??= get_bloginfo( 'url' );
    }

    /**
     * Get the license name.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name ??= get_bloginfo( 'name' );
    }

    /**
     * Get the timestamp of the next validation.
     *
     * @return int
     */
    public function get_next_validation(): int {
        return (int) wp_next_scheduled( $this->get_hook(), array( $this->get_id() ) );
    }

    /**
     * Register the license.
     *
     * @param  array{id: string, key: string, object: string, activation_limit: int, activations_count: int, revokes_at: ?int, status: 'active'|'inactive'|'revoked', current_release: ?string, price: string, product: string, variant: ?string, created_at: ?int, updated_at: ?int}|array{http_status: string, code: string, message: string} $license License data.
     * @return static
     *
     * @throws \InvalidArgumentException If the license data is invalid.
     * @throws \RuntimeException If the license data cannot be set.
     */
    public function register( array $license ): static {
        if ( ! $this->validate_data( $license ) ) {
            throw new \InvalidArgumentException( 'Invalid license data.' );
        }

        $res = $this->set_props(
            array_filter(
                array(
                    'license_id'  => $license['id'],
                    'usage_count' => $license['activations_count'],
                    'usage_limit' => $license['activation_limit'],
                    'created_at'  => $license['created_at'],
                    'revokes_at'  => $license['revokes_at'],
                    'status'      => $license['status'] ?? 'active',
                    'registered'  => true,
                ),
            ),
        );

        if ( is_wp_error( $res ) ) {
            throw new \RuntimeException( esc_html( $res->get_error_message() ), 0 );
        }

        return $this;
    }

    /**
     * Activate the license.
     *
     * @param  array{id?: string, object: string, name?: string, counted: bool, license?: string, created_at: int, updated_at?: int, }|array{http_status: string, type: string, code: string, message: string} $activation Activation data.
     * @return static
     *
     * @throws \InvalidArgumentException If the activation data is invalid.
     * @throws \RuntimeException If the activation data cannot be set.
     */
    public function activate( array $activation ): static {
        if ( ! $this->validate_data( $activation ) ) {
            throw new \InvalidArgumentException( 'Invalid license data.' );
        }

        $counted = $activation['counted'] ?? false;

        $res = $this->set_props(
            array(
                'activated'     => true,
                'activation_id' => $activation['id'],
                'validated_at'  => time(),
                'status'        => 'active',
                'usage_count'   => $counted ? $this->get_usage_count() + 1 : $this->get_usage_count(),
            ),
        );

        if ( is_wp_error( $res ) ) {
            throw new \RuntimeException( esc_html( $res->get_error_message() ), 11 );
        }

        return $this;
    }

    /**
     * Validate the license.
     *
     * @param  array{id?: string, object: string, name?: string, counted: bool, license?: string, created_at: int, updated_at?: int, fingerprint: string, }|array{http_status: string, type: string, code: string, message: string} $activation Activation data.
     * @return static
     *
     * @throws \InvalidArgumentException If the activation data is invalid.
     * @throws \RuntimeException If the activation data cannot be set.
     */
    public function validate( array $activation ): static {
        $res = $this->validate_data( $activation ) &&
            $activation['fingerprint'] === $this->get_fingerprint() &&
            $activation['license'] === $this->get_license_id()
            ? $this->set_props(
                array(
                    'validated_at' => time(),
                    'status'       => 'active',
                ),
            )
            : throw new \InvalidArgumentException( 'Invalid license data.' );

        if ( is_wp_error( $res ) ) {
            throw new \RuntimeException( esc_html( $res->get_error_message() ), 12 );
        }

        return $this;
    }

    /**
     * Enable license validation.
     *
     * @return static
     */
    public function enable_validation(): static {
        $hook = $this->get_hook();
        $args = array( $this->get_id() );

        if ( ! $this->get_next_validation() ) {
            wp_schedule_event( strtotime( 'tomorrow midnight' ), 'twicedaily', $hook, $args );
        }

        return $this;
    }

    /**
     * Disable license validation.
     *
     * @return static
     */
    public function disable_validation(): static {
        $hook = $this->get_hook();
        $args = array( $this->get_id() );

        if ( $this->get_next_validation() ) {
            wp_unschedule_event( wp_next_scheduled( $hook, $args ), $hook, $args );
        }

        return $this;
    }

    /**
     * Check if the license ID is valid.
     *
     * @param  string $id License ID.
     * @return bool
     */
    public function is_valid( string $id ): bool {
        return ! $this->get_id() || $this->get_id() === $id;
    }

    /**
     * Is the license registered?
     *
     * @return bool
     */
    public function is_registered(): bool {
        return true === $this->get_registered();
    }

    public function is_activated(): bool {
        return true === $this->get_activated() && $this->get_activation_id() !== $this->def_key;
    }

    /**
     * Check if the license is counted.
     *
     * @return bool
     */
    public function is_counted(): bool {
        return true === $this->get_counted();
    }

    public function is_local(): bool {
        return ! filter_var(
            $this->get_activation_ip(),
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) ||
        ! $this->is_counted();
    }

    protected function get_default_data(): array {
        return array(
            'activation_id'  => $this->def_key,
            'аctivation_url' => $this->get_fingerprint(),
            'activation_ip'  => null,
            'license_id'     => $this->def_key,
            'license_key'    => $this->def_key,
            'registered'     => false,
            'activated'      => false,
            'counted'        => false,
            'status'         => 'revoked',
            'usage_count'    => 0,
            'usage_limit'    => 0,
            'created_at'     => null,
            'revokes_at'     => null,
            'updated_at'     => null,
            'validated_at'   => null,
        );
    }

    /**
     * Validate the license data.
     *
     * @param  array<string,mixed> $data License data.
     * @return bool
     */
    private function validate_data( array $data ): bool {
        return ! isset( $data['code'] ) && ! isset( $data['message'] );
    }

    private function get_hook(): string {
        return "surecart_{$this->get_id()}_license_validation";
    }
}
