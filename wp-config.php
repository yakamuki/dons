<?php
/**
 * Οι βασικές ρυθμίσεις για to WordPress
 *
 * Το wp-config.php χρησιμοποιείται από την δέσμη ενεργειών κατά την
 * διαδικασία εγκατάστασης. Δεν χρειάζεται να χρησιμοποιήσετε τον ιστότοπο, μπορείτε
 * να αντιγράψετε αυτό το αρχείο ως "wp-config.php" και να συμπληρώσετε τις παραμέτρους.
 *
 * Αυτό το αρχείο περιέχει τις ακόλουθες ρυθμίσεις:
 *
 * * MySQL ρυθμίσεις
 * * Κλειδιά ασφαλείας
 * * Πρόθεμα πινάκων βάσης δεδομένων
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL ρυθμίσεις - Μπορείτε να λάβετε αυτές τις πληροφορίες από τον φιλοξενητή σας ** //
/** Το όνομα της βάσης δεδομένων του WordPress */
define( 'DB_NAME', 'dons_db' );

/** Ψευδώνυμο χρήσης MySQL */
define( 'DB_USER', 'dons_admin' );

/** Συνθηματικό βάσης δεδομένων MySQL */
define( 'DB_PASSWORD', 'EpHiBGi71XzvuSvW' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Charset της βάσηςη δεδομένων που θα χρησιμοποιηθεί στην δημιουργία των πινάκων. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Τύπος Collate της βάσης δεδομένων. Μην το αλλάζετε αν έχετε αμφιβολίες. */
define('DB_COLLATE', '');

/**#@+
 * Μοναδικά κλειδιά πιστοποίησηςη και Salts.
 *
 * Αλλάξτε τα σε διαφορετικά μοναδικές φράσεις!
 * Μπορείτε να δημιουργήσετε χρησιμοποιώντας {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Μπορείτε να τα αλλάξετε οποτεδήποτε για να ακυρώσετε τα υπάρχοντα cookies. Θα υποχρεώσει όλους χρήστες να επανασυνδεθούν.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '4sX&A+<dqTa#mK5>SJtZLZeb8h?aYkm{4?}Z<j<rcArsa!gz;S,mfzeC^I[CrmZ0' );
define( 'SECURE_AUTH_KEY',  '2VSoqd u5YsJHW6{/CZ$W.cUTR~/aic<Wn$3TprM@SZy`yfSpn3_<yyBbP+!Znfa' );
define( 'LOGGED_IN_KEY',    'S%,53w(}I3Bn4-MlU|#`t@fV.n#-zU9Sga%4~Cks4fNeL@CrejeD:FJQ -<,GIeM' );
define( 'NONCE_KEY',        ' Q0^,Jr-7U]pW>&=,2)ZdtXLf1Ults22#x#RGtN#{a]3(k9iB?&.V7$E!Vtqt6Ae' );
define( 'AUTH_SALT',        '>i!4^CuU~mj?Q#$lj-D2qC.6xXF4EcZx/Sx<xZL`G){Y]kG]Ocu/|A2?~3lP#^`f' );
define( 'SECURE_AUTH_SALT', 'Wo8Jc`Yrd& ,lYF>4IA3XQ-k15E+6{e2k;9f}8qcdu[OS/9oBTRjdk^WC*}a*I}Z' );
define( 'LOGGED_IN_SALT',   '![uKa%)sT7H E{0G>$]wgsi b[HE>z%pw8G.a9G=*G)8$m<L{.XIoROMU,+ A63r' );
define( 'NONCE_SALT',       'JvZ@/je1h!prs`y+)2uaKs{TY0~ A>/kjiil`Qs~i.|klJvr-hnpDl!AW@Z[`RV6' );

/**#@-*/

/**
 * Πρόθεμα Πίνακα Βάσης Δεδομένων του WordPress.
 *
 * Μπορείτε να έχετε πολλαπλές εγκαταστάσεις σε μια βάση δεδομένων αν δώσετε σε κάθε μία
 * ένα μοναδικό πρόθεμα. Μόνο αριθμοί, γράμματα και κάτω παύλα παρακαλούμε!
 */
$table_prefix  = 'wp_';

/**
 * Για προγραμματιστές: Κατάσταση Απασφαλμάτωσης WordPress (Debugging Mode).
 *
 * Αλλάξτε το σε true για να ενεργοποιήσετε την εμφάνισης ειδοποιήσεων για την διαδικασία ανάπτυξης.
 * Η χρήση WP_DEBUG προτείνεται για τους δημιουργούς προσθέτων και θεμάτων
 * στο περιβάλλον ανάπτυξης τους.
 *
 * Για πληροφορίες για άλλες σταθερές που μπορούν να χρησιμοποιηθούν για απασφαλμάτωση,
 * επισκευθείτε το Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Αυτό είναι όλο, σταματήστε γράφετε! Χαρούμενο blogging. */

/** Η απόλυτη διαδρομή τον κατάλογο του WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Ορίζει τις μεταβλητές και τα περιλαμβανόμενα αρχεία WordPress. */
require_once(ABSPATH . 'wp-settings.php');
