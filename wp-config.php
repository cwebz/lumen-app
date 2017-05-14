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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'gmixclan_wp1');

/** MySQL database username */
define('DB_USER', 'gmixclan_wp1');

/** MySQL database password */
define('DB_PASSWORD', 'V#4vDhOue29FtXe@Y0(30#]4');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'YIII2j2mbr3sXp5DH21xGos0bxdnIK1tppwRw7Grjy225iau4gt27aWAp9ACMA7V');
define('SECURE_AUTH_KEY',  'U1oHAmouq7obXRwUSrBbpxhXqV6TpVz0QDmqn1tgnvp8ZeX5C4rueHlLGkESgPUb');
define('LOGGED_IN_KEY',    'mz5otyjHq9W5UNBX6hKq6HgRszfFuD0RA7z1stKERdYLCOvtnA1pfyGt74bYPRhT');
define('NONCE_KEY',        'BeLzrEr0nisOd6iu1YW1UsGyN8sshMyO2iLu6dmpmqgLY1SzkqoiDA8qcSCX4rTu');
define('AUTH_SALT',        '0GxRL8L39Fw8KCu4DReCcD8syKIuTePtoO1zdeTYMgJJlHqEAFcTS9MldC9LQLrF');
define('SECURE_AUTH_SALT', 'lKAjOBoELnveV17Ta506LkqX3biOaVGQxG1n8tknN1eUQKG5Nt8qikQzDsBvDFkp');
define('LOGGED_IN_SALT',   's0n6KdCNMFxpqKTP9a8cXdVQ8GxzUF7TEbKIpNtVEytkStCjwlH9EBjdg277ztuP');
define('NONCE_SALT',       '8q43D2RKv8x6N0yUYzW9NaOaHj4oUBn4RIl9x8X1PqJqyY1uXDZVYkDAXsUtyi4T');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
