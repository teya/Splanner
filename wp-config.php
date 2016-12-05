<?php
date_default_timezone_set('Asia/Manila');
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

define('WPLANG', '');
define('WP_HOME','http://localhost/splan/');
define('WP_SITEURL','http://localhost/splan/');



// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
//define( 'WPCACHEHOME', '/home/seoweb91/public_html/admin/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('WP_MEMORY_LIMIT', '256M');
//define('WP_CACHE', true); //Added by WP-Cache Manager
define('DB_NAME', 'splan_db');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         'oH$vR8EIPh@pa<gMew8yQdMjzYSMWtK54e*\`Bv9>?=#Y>LU06/iN;rDyp4o2dEuTzp3U0');
define('SECURE_AUTH_KEY',  '91>wXjCYiO?YhKdlgjDJ|F^ZIG*7Za>kA45Zb_glbb*J69E!aS**mGJ1NggzES43g10');
define('LOGGED_IN_KEY',    '(_<pz$OiNfIfMPZm4w1*ss>4CMoAQMFkYy?6m0(uYHLwHS1QdAYEHGeD97t8R9L9s/tNg$*S_sgf$<ps3!OZ8o');
define('NONCE_KEY',        '~jd/UTr6/:WybkKN*EMbsVNjRuv>X@0^KfVl~ivn9hBEaEF5pSoCsknC54n=|L*uo?SwMye9');
define('AUTH_SALT',        'lHhY#g*^XWxsXcmmZ3^ij4\`|2(*mqb4$3i56cZ@3m5SY:HZfwX#9FNcAvH=?5FW7S/)R;>mc\`>6ZoL');
define('SECURE_AUTH_SALT', 'aZ3ETJWHLjyelZbz$_9i;hu;rRXBFyV@@EjcvB2^dRe!Fi@de<ZqJE0C_ilTVbejnkY-QTw$');
define('LOGGED_IN_SALT',   '4CKt>?mStLruVs|n*NQ1jotBMsH~X7zgVUF@iuV1ufIJ)ah3Mucs=m)/8G~88Hr~S');
define('NONCE_SALT',       '<J6X>)mFxJk:0@DL9p!$so^mAniASXsPM/^\`FFB@s9x?J=R^\`DMM\`r$kl#TaI0~6#lbMd2gQB');

/**#@-*/
define('AUTOSAVE_INTERVAL', 600 );
define('WP_POST_REVISIONS', 1);
define('WP_CRON_LOCK_TIMEOUT', 120 );
define('WP_AUTO_UPDATE_CORE', true );
define('DISABLE_WP_CRON', true);
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );
