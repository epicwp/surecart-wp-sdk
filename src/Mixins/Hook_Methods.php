<?php

namespace SureCart\WP\Mixins;

trait Hook_Methods {
    /**
     * Whether the hooks have been loaded.
     *
     * @var bool
     */
    protected bool $hooked = false;

    /**
     * Set the hooked property to true.
     *
     * @return void
     */
    public function on_initialize(): void {
        $this->hooked = true;
    }

    /**
     * Load the hooks for the class.
     */
    public function load_hooks(): void {
        if ( $this->hooked ) {
            return;
        }

        foreach ( $this->get_hooks() as $tag => $opts ) {
            $cb = match ( $opts['type'] ) {
                'action' => 'add_action',
                'filter' => 'add_filter',
            };

            foreach ( (array) $opts['methods']  as $method ) {
                $cb( $tag, array( $this, $method ), $opts['priority'], $opts['args'] );
            }
        }
    }

    /**
     * Get the hooks for the class.
     *
     * @return array<string,array{
     *   methods: array<string>|string,
     *   type: 'action'|'filter',
     *   priority: int,
     *   args: int,
     * }>
     */
    abstract protected function get_hooks(): array;
}
