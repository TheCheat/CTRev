<?php

/**
 * Project:            	CTRev
 * File:                config_global.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Определение основных констант движка
 * @version           	1.00
 */

if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * @const ENGINE_VERSION версия движка
 */
define('ENGINE_VERSION', '1.00');
/**
 * @const ENGINE_STAGE стадия разработки
 */
define('ENGINE_STAGE', 'RC2');



/**
 * @const MODULES_PATH путь к модулям, относительно корня системы
 */
define('MODULES_PATH', 'modules');
/**
 * @const BLOCKS_PATH путь к блокам, относительно модулей
 */
define('BLOCKS_PATH', 'blocks');
/**
 * @const ADMIN_MODULES_PATH путь к модулям АЦ, относительно корня системы
 */
define('ADMIN_MODULES_PATH', 'admincp/modules');
/**
 * @const ADMIN_PAGES_PATH путь к старинцам АЦ, относительно корня системы
 */
define('ADMIN_PAGES_PATH', 'admincp/pages');
/**
 * @const THEMES_PATH путь к темам, относительно корня системы
 */
define('THEMES_PATH', 'themes');
/**
 * @const TEMPLATES_PATH путь к шаблонам, относительно темы
 */
define('TEMPLATES_PATH', 'templates');
/**
 * @const LANGUAGES_PATH путь к языковым пакетам, относительно корня системы
 */
define('LANGUAGES_PATH', 'languages');
/**
 * @const PLUGINS_PATH путь к плагинам, относительно корня системы
 */
define('PLUGINS_PATH', 'include/plugins');
/**
 * @const PLUGINS_INC путь к инклуд-файлам плагинов, относительно пути к плагинам
 */
define('PLUGINS_INC', 'inc');
/**
 * @const PLUGINS_REPLACED путь к логам заменённых выражений, относительно пути к плагинам
 */
define('PLUGINS_REPLACED', 'replaced');
/**
 * @const CLASS_ALIASES алиасы для классов(при отсутствии функции)
 */
define('CLASS_ALIASES', "include/system/aliases.php");



/**
 * @const unended бесконечность и далее...
 */
define('unended', "&#8734;");
/**
 * @const empty_option пустая опция
 */
define('empty_option', "<option value='0'>-----</option>");
/**
 * @const UNIQUE_VALUE_ERROR ошибка неуникальности значения
 */
define('UNIQUE_VALUE_ERROR', 1062);

/**
 * @const IN_DEVELOPMENT установите 0, если движёк находится не в стадии разработки
 */
define('IN_DEVELOPMENT', 1);



/**
 * @const DEFAULT_THEME тема по-умолчанию, на случай, если не удалось подключить БД и прочитать из конфигурации
 */
define('DEFAULT_THEME', 'CTRev');
/**
 * @const DEFAULT_LANG язык по-умолчанию, на случай, если не удалось подключить БД и прочитать из конфигурации
 */
define('DEFAULT_LANG', 'ru');



// Константы для конфига, напр.
/**
 * @const ALLOWED_AVATAR_URL разрешено загружать аватары по URL
 */
define('ALLOWED_AVATAR_URL', '1');
/**
 * @const ALLOWED_AVATAR_PC разрешено загружать аватары с компьютера
 */
define('ALLOWED_AVATAR_PC', '2');

/**
 * @const ALLOWED_IMG_URL разрешено загружать скриншоты по URL
 */
define('ALLOWED_IMG_URL', '1');
/**
 * @const ALLOWED_IMG_PC разрешено загружать скриншоты с компьютера
 */
define('ALLOWED_IMG_PC', '2');
?>