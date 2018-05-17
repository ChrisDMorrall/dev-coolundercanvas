<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'KHs0NlbPlJ6kqLke4rdiGD44/1slVTDn2kfsnm6cULWceb1UclZD8sFuNEX0u6oP2S47b9wVOP42/J1QkL/+KA==');
define('SECURE_AUTH_KEY',  'd/8E3p+AzbuDCF5VHF5nWFG8YENCjtdyKYsz6P2llNAgP91C1O19rB4ch6Ar5/kKoZBJUjF+kXMGJ0DRUU/VJQ==');
define('LOGGED_IN_KEY',    'jfl4qK4klvcdBNPcbooboIZzRe7SYZHO4m0FsAwVxf91XGVXgLMFs0FJ3mThgFqo0Hh7sYcc6BT+UMbxLuaYHA==');
define('NONCE_KEY',        'TpjDzkduq8ZpwQUAKPd9G1PBf+B8G/y7JYZPMhIH18vr9Bh2uJWwn/urs8nNdQi9llby66n3v9/MNdALy4Xcfg==');
define('AUTH_SALT',        '5jzgBbr3B9YTzNROhEj2o2LFdn+aMStgcnp6bZOcGD6rEdqkOG3TZuVQdwuH8eULdLivhEMdYBXGfanT8TAbOw==');
define('SECURE_AUTH_SALT', 'jYlH+Pcvt36pBVF4GuDnI3EW3EPDDufK9P3efhQ2k/LZNe/7ii3j58ANWz7DtowHQn2Uz49V/A2lkJnMLFW42g==');
define('LOGGED_IN_SALT',   'mo0T/1ZOBuW1fEfCLpV49DPSnEP6njSnWK8RCb1O5rj3npynv6IIWDj49d7Y2QZqc8rE0lPvhBdrLO4x6pBrJQ==');
define('NONCE_SALT',       'gxaedM7A9fuBjIdD2KOaMp5SUcnqoe+Jkn7W1s6fluFIjev/Na6AMP5pYfeFITw2GrAhMf5/FX4mpufxp7VHUw==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';





/* Inserted by Local by Flywheel. See: http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy */
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
}

/* Inserted by Local by Flywheel. Fixes $is_nginx global for rewrites. */
if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) && strpos( $_SERVER['SERVER_SOFTWARE'], 'Flywheel/' ) !== false ) {
	$_SERVER['SERVER_SOFTWARE'] = 'nginx/1.10.1';
}
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
