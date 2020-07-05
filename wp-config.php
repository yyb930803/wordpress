<?php
/**
 * Il file base di configurazione di WordPress.
 *
 * Questo file viene utilizzato, durante l’installazione, dallo script
 * di creazione di wp-config.php. Non è necessario utilizzarlo solo via
 * web, è anche possibile copiare questo file in «wp-config.php» e
 * riempire i valori corretti.
 *
 * Questo file definisce le seguenti configurazioni:
 *
 * * Impostazioni MySQL
 * * Prefisso Tabella
 * * Chiavi Segrete
 * * ABSPATH
 *
 * È possibile trovare ultetriori informazioni visitando la pagina del Codex:
 *
 * @link https://codex.wordpress.org/it:Modificare_wp-config.php
 *
 * È possibile ottenere le impostazioni per MySQL dal proprio fornitore di hosting.
 *
 * @package WordPress
 */
define('WP_SITEURL', 'https://winefully.com');
define('WP_HOME', 'https://winefully.com');

// ** Impostazioni MySQL - È possibile ottenere queste informazioni dal proprio fornitore di hosting ** //
/** Il nome del database di WordPress */
define( 'DB_NAME', 'theit255_winefully' );

/** Nome utente del database MySQL */
define( 'DB_USER', 'theit255_wineful' );

/** Password del database MySQL */
define( 'DB_PASSWORD', '5c(xXhf$z,Up' );

/** Hostname MySQL  */
define( 'DB_HOST', 'localhost' );

/** Charset del Database da utilizzare nella creazione delle tabelle. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Il tipo di Collazione del Database. Da non modificare se non si ha idea di cosa sia. */
define('DB_COLLATE', '');

/**#@+
 * Chiavi Univoche di Autenticazione e di Salatura.
 *
 * Modificarle con frasi univoche differenti!
 * È possibile generare tali chiavi utilizzando {@link https://api.wordpress.org/secret-key/1.1/salt/ servizio di chiavi-segrete di WordPress.org}
 * È possibile cambiare queste chiavi in qualsiasi momento, per invalidare tuttii cookie esistenti. Ciò forzerà tutti gli utenti ad effettuare nuovamente il login.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'b]vg@/qXY&Q5t+Mo.hBc<1v,JD>=w@ll,t$(q6e%mb|f;r5/4YUjBz]8O`Kz.h3K' );
define( 'SECURE_AUTH_KEY',  'b727a41W{b3f}IGUh+]RH(ga2)|!&v[{F!{=oZ*yBkCwNeBUt:4<%HgyrIw~G#jv' );
define( 'LOGGED_IN_KEY',    '3`hvEy=dU#>1Dc`4wI}-U1kQ=jvIH 1J]bQuqrg=KrI[u)!uQe&,q@-D:xMqVy+.' );
define( 'NONCE_KEY',        'v%!!?gKM*g3S=xgWfT9P+g?btBD_&K;7C/~rL=Toz:opuBw{<x&Gf0FZo.Me4Y3*' );
define( 'AUTH_SALT',        'Icj1y&]u|sI)9QG9DNcW{<aB%Q?)_P%{UMc{yy/&>B{&=Nc QPc}pp%L#tyLQ]2q' );
define( 'SECURE_AUTH_SALT', 'O7-6yL&Z@KT@AE|lCC6[K8?gpO5`jL1K2.#T 9h|GqqJ{`qW*%Vmot&gV9~=8DK~' );
define( 'LOGGED_IN_SALT',   'I23*>;D,NMeSz2&6Z26,qHfS-+DoVO:Da.V2.Ke|XxlgV@h4Z.XQ$LFU]C7K#14i' );
define( 'NONCE_SALT',       '7aRCKrB7BX-TuFw7=#Y&;x]J;8@#F5;U<MZf*?2F_)rhvMRk!Gvp++( &aVC{v5>' );

/**#@-*/

/**
 * Prefisso Tabella del Database WordPress.
 *
 * È possibile avere installazioni multiple su di un unico database
 * fornendo a ciascuna installazione un prefisso univoco.
 * Solo numeri, lettere e sottolineatura!
 */
$table_prefix = 'wn_';

/**
 * Per gli sviluppatori: modalità di debug di WordPress.
 *
 * Modificare questa voce a TRUE per abilitare la visualizzazione degli avvisi
 * durante lo sviluppo.
 * È fortemente raccomandato agli svilupaptori di temi e plugin di utilizare
 * WP_DEBUG all’interno dei loro ambienti di sviluppo.
 */

define('WP_MEMORY_LIMIT','256M' );
define( 'WP_DEBUG', false );
 

/** Path assoluto alla directory di WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Imposta le variabili di WordPress ed include i file. */
require_once(ABSPATH . 'wp-settings.php');

# Disables all core updates. Added by SiteGround Autoupdate:
define( 'WP_AUTO_UPDATE_CORE', false );

@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system

