<?php
/**
 * Основне поставке Вордпреса.
 *
 * Ова датотека се користи од стране скрипте за прављење wp-config.php током
 * инсталирања. Не морате да користите веб место, само умножите ову датотеку
 * у "wp-config.php" и попуните вредности.
 *
 * Ова датотека садржи следеће поставке:
 * * MySQL подешавања
 * * тајне кључеве
 * * префикс табеле
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL подешавања - Можете добити ове податке од свог домаћина ** //
/** Име базе података за Вордпрес */
define('DB_NAME', 'nomad_db');

/** Корисничко име MySQL базе */
define('DB_USER', 'nomad_db');

/** Лозинка MySQL базе */
define('DB_PASSWORD', '9z301Aj9Calrl1lm');

/** MySQL домаћин */
define('DB_HOST', 'localhost');

/** Скуп знакова за коришћење у прављењу табела базе. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Не мењајте ово ако сте у сумњи. */
define('DB_COLLATE', '');

/**#@+
 * Јединствени кључеви за аутентификацију.
 *
 * Промените ово у различите јединствене изразе!
 * Можете направити ово користећи {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org услугу тајних кључева}
 * Ово можете променити у сваком тренутку да бисте поништили све постојеће колачиће.
 * Ово ће натерати кориснике да се поново пријаве.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'bDFBA32oGke}Y>5Mjj{:YcT,#%|B5]gD9~mf5$Xw.(S}w8&NHj?q!6En]RfhzN#R');
define('SECURE_AUTH_KEY',  'N~jl^oj(r!CJCd1yz%&)YDY-irCixM)R:!PTVPx=n=HsV#z^UeIm7K_YKjw^EV-1');
define('LOGGED_IN_KEY',    '{_xdM<&@sL7I{Yl-: unmm]mks4?Bj5|?Hpu4UOern2ZYJElGcD=@uvx5-h*B%^9');
define('NONCE_KEY',        'YH*5+>u&ARE^y@X~o.W!l[h[*>[Lp$@.-eM`H)zc$x>kdF??A7lv2SNnivozr2 k');
define('AUTH_SALT',        'zZ|qT1,9woW{BMh|:!N;Tr^:U7+Us:`W:6rvi@o)6khab4FlRwO:GG-be8/@nj+C');
define('SECURE_AUTH_SALT', 'BTp@hRnjb#*}&#(NiU*_n{|0xgYK[P1L$TO226l] :[]* n_cV(1obe#JPx{qx!+');
define('LOGGED_IN_SALT',   'k>H&*u7q_Ugn@Y{diGKHuEN0zVH){sdj]D@yFCAx21LKp%CAw`fJiol=A!%nR!}d');
define('NONCE_SALT',       '^;tJ~T)RT ^<}%zl}0!t!H^*8ZdOEp&Trd<^Qn`zA6ps!T<6ZD.{a).-,.@&5&oR');

/**#@-*/

/**
 * Префикс табеле Вордпресове базе података.
 *
 * Можете имати више инсталација Вордпреса у једној бази уколико
 * свакој дате јединствени префикс. Само бројеви, слова и доње цртице!
 */
$table_prefix  = 'wp_';

/**
 * За градитеље: исправљање грешака у Вордпресу ("WordPress debugging mode").
 *
 * Промените ово у true да бисте омогућили приказ напомена током градње.
 * Веома се препоручује да градитељи тема и додатака користе WP_DEBUG
 * у својим градитељским окружењима.
 *
 * За више података о осталим константама које могу да се
 * користе током отклањања грешака, посетите Документацију.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* То је све, престаните са уређивањем! Срећно блоговање. */

/** Апсолутна путања ка Вордпресовом директоријуму. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Поставља Вордпресове променљиве и укључене датотеке. */
require_once(ABSPATH . 'wp-settings.php');
