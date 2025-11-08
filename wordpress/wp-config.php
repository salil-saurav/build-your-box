<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
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
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'EH`31.>9qz@ulh!mw[<vDz;lW^4j4jdrFv#zLs?B[q4}ca+xf0}=n62JE~S]zi5&' );
define( 'SECURE_AUTH_KEY',   'QpraXE=LH8Ugxf:j2L3U(5KOY>,(qS/fZAKO#0<OUE]!l`rNiS@*2`HZjR@;ei];' );
define( 'LOGGED_IN_KEY',     'T9w/7jl4y+n$`#fC@7tasaFlL4z>&2mS(:H6V,uty( /P1@{kYmehA`7Ho]`U &}' );
define( 'NONCE_KEY',         '#s!P6w?J[./,>DY4:m.5QLx=Jgx*Y{4wT^]+e }.pd.T&sJ/kv|,%>#Yeyjewygt' );
define( 'AUTH_SALT',         'w+WdNFlCG3Ec4a=caZICd@*G0bb!;GuJg^OI0HKuo`mJt;3,bcMZ7v>5Z,zPY>D-' );
define( 'SECURE_AUTH_SALT',  'e2w1-~y;]mQ7n>%u {+33!P1p:JnsWLgPK)5!CVE]Olavn#V:Kgtw>7$_qiYD2OX' );
define( 'LOGGED_IN_SALT',    'PT{l.g[4J.Cfs)QRay8%q&T0q[ObWb@V(DgrIkwb<]:w zGS&:R~QnwKBURfF]=q' );
define( 'NONCE_SALT',        '(sWY|qBLci0L~7MTi55|1n/y9A5>m2Y- I!v{?>i&(vLA8??Q%;op5#d.V_.ECJu' );
define( 'WP_CACHE_KEY_SALT', 'qLF}@K*vtT!%fN,Ex;qKsWxx=~a*wenS@]#[*X{yic<HXgPa}CAV8REKeT5Se 4h' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
