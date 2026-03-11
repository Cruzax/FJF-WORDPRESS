<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'fjf' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ka2f:nXTp<6,6]{fs!-*@M@PSZE|}ks})~1W* w::m<WT%C)}K<*4>n V%%2OCkV' );
define( 'SECURE_AUTH_KEY',  ' 4:fgJfN;rL:c;(J>+y,YIzmUM]?m;V`uYu-|eF~]J@knMyfeJ{0!hx2P/*lay!S' );
define( 'LOGGED_IN_KEY',    '1}cf 9V$#%LdFfgcr|+.M26#Q1Q)$[SzMV%1-W50?)CG-fPr? ?)}|IA={4/ fZ2' );
define( 'NONCE_KEY',        'wY0)bKfcL8~fSz1z_t,n6;P#G2iqkl`|JYO5p.Rpb,8-$E/nf$XJ.i<k$,jS=PBM' );
define( 'AUTH_SALT',        'f1,a:N|+Y`I4C#%Kp%%=en#=Vdc8Q;;tZ[M&<am!x<>)*:w&a(4rUDKRq,3j#s@K' );
define( 'SECURE_AUTH_SALT', 'HyFG=wG[yWpK(yI P HC~<4-e_9) 2*kq[cP<8F%/7$@w>I%+I-35WM9Z=lbsPd?' );
define( 'LOGGED_IN_SALT',   '%di;`&B*,|-vLIJ`G@q&A@}xZ<][=`>7F&C[;V,XCtLmb)]L0G>-Q9V(3bv9Iw[,' );
define( 'NONCE_SALT',       'DfllImt[/]GTcPHP<>&g&h2+a<#1:*+[fb6AL^7$PJl|lL!s7@%a7NKuNuxf{th:' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
