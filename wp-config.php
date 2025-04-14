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
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'S5$rtV7Ecueb`;~b_,ObN7ROnq-#JsGfXF}=B-%:{Fcxa6>|sq+dhk?qKe3_fIYx' );
define( 'SECURE_AUTH_KEY',  '`9=;(U!s64W|ong*blMDbb4^!zI>8;k_)iyY80|sCW_oYshze;Tf[w5C1B6D,}VC' );
define( 'LOGGED_IN_KEY',    '_rN<rS`fi^C?(]6)r3J4UIXz+p;DU+0]kfC*7*R$z[NXzXT|6}mz~yG/_?iiTa$i' );
define( 'NONCE_KEY',        '#L9.(qGIR{%eP)J{{44+^?QfU&(9[bi1-CtJ*A(Ak`yNdJ?L|jMbtb |bd3>^nI*' );
define( 'AUTH_SALT',        'D&]3q7j^{^8$<ZI:%j_+TF>-Au}zAf)$d9GagUEq?-VrldhQ&Y{Aof`l]Umpi43(' );
define( 'SECURE_AUTH_SALT', 'e/X}Ip(7-cg!jGh/5LKL<Y6_FEz.AzP^g>IVn8[ADh/aq;,R+N(6[YPEw(72f$5T' );
define( 'LOGGED_IN_SALT',   'd,*`y4$^$]>j<@lPb0ofsk%XZo}&{XCC^].VzWFfC }r[C9tUgjvbr#.Muf`#IH3' );
define( 'NONCE_SALT',       'q`:;$+_FN^vXC|tA,oV66rS6QZG8?=>!eS[44C5L5v 0dw@G{M+Jn~V,?$(Cp%gI' );

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
$table_prefix = 'wp_w_';

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
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
