<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Le script de création wp-config.php utilise ce fichier lors de l'installation.
 * Vous n'avez pas à utiliser l'interface web, vous pouvez directement
 * renommer ce fichier en "wp-config.php" et remplir les variables à la main.
 * 
 * Ce fichier contient les configurations suivantes :
 * 
 * * réglages MySQL ;
 * * clefs secrètes ;
 * * préfixe de tables de la base de données ;
 * * ABSPATH.
 * 
 * @link https://codex.wordpress.org/Editing_wp-config.php 
 * 
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'sfnettoyage');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'sfnettoyage');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'sfnettoyage33');

/** Adresse de l'hébergement MySQL. */
define('DB_HOST', 'localhost');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8mb4');

/** Type de collation de la base de données. 
  * N'y touchez que si vous savez ce que vous faites. 
  */
define('DB_COLLATE', '');

/**#@+
 * Clefs uniques d'authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant 
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n'importe quel moment, afin d'invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '{6RImE-a5G39mJ`%/WE?u:j/Eg>xOV@~*#K7p8zGve)~njdr4bBDF*?TXD[jt,jw');
define('SECURE_AUTH_KEY',  '|QP0i2vhlHmcM<Rjq1mU|bGkzQxsPcb6O+&JER4R?w&f+L3YV]h0s.k^/Ia<F--J');
define('LOGGED_IN_KEY',    '&Ze3FW0?5`enOf^!mn*= QO06yB.2k!647^7+cK)5qoR7&,xL: 3ys<#cS!}hhW<');
define('NONCE_KEY',        'e^fL<w*2rBgi!%U8St$%&j1PI%/?(*A^G/T=Myp?]dB1# :/X;;/]]P)xUKgR;#*');
define('AUTH_SALT',        '{T_c3oerO]hXdy1XL@T!VU%:pZR oy?UP/F:+UqT|^-f7[>b,^*MNU0:q1,WOyWM');
define('SECURE_AUTH_SALT', '&p@QA$|T[VU[dm@(&<!ZVaE]A/)2U~uSymzD|0e[07-.]S:kAs+8O h]V^G}Aw08');
define('LOGGED_IN_SALT',   '*cmW`SHM{M8gyZLC<{08GR~Ou3a>CSjDNtptz {|PL-.}|I@mstUthu#~SIcP33:');
define('NONCE_SALT',       ']fx&h.tz 6{nX-n:#&KBK<M7?TB yzcau>4L{:ly0s%ebnbya5+lo#lQvMpJ-<ca');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique. 
 * N'utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés!
 */
$table_prefix  = 'wp_';

/** 
 * Pour les développeurs : le mode déboguage de WordPress.
 * 
 * En passant la valeur suivante à "true", vous activez l'affichage des
 * notifications d'erreurs pendant votre essais.
 * Il est fortemment recommandé que les développeurs d'extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de 
 * développement.
 * 
 * Pour obtenir plus d'information sur les constantes 
 * qui peuvent être utilisée pour le déboguage, consultez le Codex.
 * 
 * @link https://codex.wordpress.org/Debugging_in_WordPress 
 */ 
define('WP_DEBUG', false); 

/* C'est tout, ne touchez pas à ce qui suit ! Bon blogging ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');