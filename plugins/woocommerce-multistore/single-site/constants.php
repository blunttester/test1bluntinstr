<?php
define( 'WOO_MSTORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_MSTORE_URL', plugins_url( '', __FILE__ ) );
define( 'WOO_MSTORE_APP_API_URL', 'https://woomultistore.com/index.php' );

define( 'WOO_MSTORE_PLUGIN_NAME', 'WooMultistore' );
define( 'WOO_MSTORE_VERSION', '4.1.5' );
define( 'WOO_MSTORE_DB_VERSION', '1.0' );

define( 'WOO_MSTORE_PRODUCT_ID', 'WCMSTORE' );
define( 'WOO_MSTORE_INSTANCE', str_replace( array( 'https://', 'http://' ), '', site_url() ) );

define( 'WOO_MSTORE_SINGLE_TEMPLATES_PATH', dirname( __FILE__ ) . '/templates/' );
define( 'WOO_MSTORE_SINGLE_INCLUDES_PATH', dirname( __FILE__ ) . '/includes/' );

define( 'WOO_MSTORE_ASSET_URL', plugins_url( '', dirname( __FILE__ ) ) );
