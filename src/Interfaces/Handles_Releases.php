<?php

namespace SureCart\WP\Interfaces;

use XWP\Updater\Interfaces\Handles_Updates;

interface Handles_Releases extends Handles_Updates {
    public function can_update( string $package_file ): bool;
}
