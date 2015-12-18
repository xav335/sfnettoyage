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
define('AUTH_KEY',         '%edso_k$@BFfHf(1Tm?)k:4*ZlIEkDa+YOB1W)SQkfM3>`#:z$Q!CDUs29Qe.qt3');
define('SECURE_AUTH_KEY',  'hKHrD5ZO2jx(_Ol|Naq5x-]y-hnl}{J4R;?E>![z=6i$TCeryu r8Y$TIcXS2}:K');
define('LOGGED_IN_KEY',    '/n%,0s9*hb:27K$cn%J8-Fdc^m|2#_._H6qqc_B.g)GbGf+ $]z+K51yz)q6.8c$');
define('NONCE_KEY',        '.Cg@BBO/esPD1!tclXq=FV(G)tM<wYic(+W&O^%4%lzPM5]`|;)|pn[gzF*SW@1L');
define('AUTH_SALT',        'sQMO]d<vPiA[8gP:o)sV]IY7-:K6@i8+Oe?I`;Zd6$Ec[x0%Hg@~#&-/*iSp@dJ&');
define('SECURE_AUTH_SALT', '9$Y|?[AtYZ_wT]Gh7^Fm]vKQY%HPT.&F*lf,TJ+=N5zD@v5O@DV++J6q|[8YuC)o');
define('LOGGED_IN_SALT',   'a`Q@6+6F?=CAj$7*0xDjQ+zp,f6,=9!c.jpFf,:a?}^+M0zm*_DAVj+VKU(*@7,$');
define('NONCE_SALT',       '`}Y(V]$-%AqIMe2rNW)>`tdJcyIY-9ubUNq>/fB2v`0WXDnl.)Rpm-O4BMtet=y^');
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