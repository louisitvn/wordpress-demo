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
define('FS_METHOD', 'direct');

/** The name of the database for WordPress */
define('DB_NAME', 'acelle_lite');

/** MySQL database username */
define('DB_USER', 'acelle_lite');

/** MySQL database password */
define('DB_PASSWORD', 'acelle_lite!');

/** MySQL hostname */
define('DB_HOST', 'acelle-demo.ckoqjetbmqdi.us-west-2.rds.amazonaws.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '`K6!~bqD^X`7wJo{,Fg9fJaDoNoR1[ 25YRe7d5_5uyT r`A5 cX=SN;-/~{QF&K');
define('SECURE_AUTH_KEY',  '#KsY@_?0?g|TTUaEk.n]apqu(<k*X6;LMGNvi1|2U f#!-XFAfKN%fY&o@OdSHbb');
define('LOGGED_IN_KEY',    'jAX!tdzvTtBf7c}$LA}~f$F;bDN1o@52[JW{K.BW8[I7NTUo(o[]dm.o85ZW6;[-');
define('NONCE_KEY',        '&A08v7PoouZ[%&E9qa|7-Ww%T=>?N2LG|Rez|AV2f`+imd+WC]1zSPs%o/=;1_SY');
define('AUTH_SALT',        'Y,m)T;?*s}6Alq7E-(o?XA;g8Y0PpJ}iY7HL`z|uMBQI h]m.b:>Iq1< AXT@ApJ');
define('SECURE_AUTH_SALT', ',B}1.4Ub{4:a)mvC-3?It:O/>@p/khr+m}8q&Myb^toueg YpbPtj=Q[SZ&r6_8x');
define('LOGGED_IN_SALT',   'QXe~-W_Kb[)i4=_1ZMN3JHLRYWKrmadL4T7~8$qvAj=GdD(YnTq+#1ckds27!Rj0');
define('NONCE_SALT',       'd[vE}}nW*2lBbFNy}%uPXOxFG=X@0#bQ~rRYCwdVbczhP)l{[r).kQbIG41s]a2k');

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
