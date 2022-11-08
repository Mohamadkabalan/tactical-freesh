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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'tacticalfreesh' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'UdzsnknfQoZcaX8U' );

/** MySQL hostname */
define( 'DB_HOST', 'devkinsta_db' );

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
define( 'AUTH_KEY',          'y8@*2nL{2:Cl%HyZGsTNTG#~AEo 3.EgD]Zhq$BO.<g:iU~ U(pZ]j6Vm#;h=j]l' );
define( 'SECURE_AUTH_KEY',   'E@XQb3GU/K)os9YkhvFukZhXA%uwU[p.m~c|I<Uuhp9pO%Dt}W~c#v;1657&izc*' );
define( 'LOGGED_IN_KEY',     'WnIN.%)3nstgb8v(!b*9&2>-`Q%L1/h+_@4{2h+-, Nv b(k9iu{/xjwSTLU45:|' );
define( 'NONCE_KEY',         'B:MfyA.Y@3:{X=^paOZr&,iv{@)D919(9-rlZ7<<`0m1pirZ))XO{(G$GqvX E[$' );
define( 'AUTH_SALT',         'K^igG.., Rpq0[^9:0]+~!AXut:;,t_(#r?=}H~kBS>WCV<UZsK#FCyfqMu*)QLh' );
define( 'SECURE_AUTH_SALT',  '?0IhJ} %<> b$Wk[ANdEmr*#>Bnwg(8N5[=H9#^<QF=#KUas.-?-c VeCt?~:/iE' );
define( 'LOGGED_IN_SALT',    'aJx%H:z8sX[_1Ps,/0QbzEa&i|cF~>C`EN*=kZN0g)MMpGl {kLwfV9-J4#H[33)' );
define( 'NONCE_SALT',        'Jsp`wpCg^*hH57O/+ZKCXGcK4esD,ol5BosHo11])#8cHM,NamJ^02C]NLS>N4f<' );
define( 'WP_CACHE_KEY_SALT', '5wB:Cvz1Qd45ulb 6 `C#s$Zo)pb$Eu2m;*~<Dt/Y@PaQAwNrK(TeT[iprIL?BQb' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




define( 'WP_DEBUG', 'true' );
define( 'WP_DEBUG_LOG', 'true' );
define( 'WP_DEBUG_DISPLAY', 'true' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
