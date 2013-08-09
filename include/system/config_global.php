<?php

/**
 * Project:            	CTRev
 * @file                include/system/config_global.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Определение основных констант движка
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * Версия движка
 */
define('ENGINE_VERSION', '1.00');
/**
 * Стадия разработки
 */
define('ENGINE_STAGE', 'Beta');



/**
 * Макс. время соединения для сокетов
 */
define('DEFAULT_SOCKET_TIMEOUT', 5);
/**
 * Макс. время выполнения скрипта
 * НЕ рекомендуется ставить 0
 */
define('MAX_SCRIPT_EXECUTION_TIME', 30);



/**
 * Путь к модулям, относительно корня системы
 */
define('MODULES_PATH', 'modules');
/**
 * Путь к блокам, относительно модулей
 */
define('BLOCKS_PATH', 'blocks');
/**
 * Путь к цветам, относительно css и изображений
 */
define('COLORS_PATH', 'colors');
/**
 * Путь к модулям АЦ, относительно корня системы
 */
define('ADMIN_MODULES_PATH', 'admincp/modules');
/**
 * Путь к страницам АЦ, относительно корня системы
 */
define('ADMIN_PAGES_PATH', 'admincp/pages');
/**
 * Путь к темам, относительно корня системы
 */
define('ADMIN_THEME', 'admin');
/**
 * Путь к темам, относительно корня системы
 */
define('THEMES_PATH', 'themes');
/**
 * Путь к шаблонам, относительно темы
 */
define('TEMPLATES_PATH', 'templates');
/**
 * Путь к языковым пакетам, относительно корня системы
 */
define('LANGUAGES_PATH', 'languages');
/**
 * Путь к плагинам, относительно корня системы
 */
define('PLUGINS_PATH', 'include/plugins');
/**
 * Путь к инклуд-файлам плагинов, относительно пути к плагинам
 */
define('PLUGINS_INC', 'inc');
/**
 * Путь к логам заменённых выражений, относительно пути к плагинам
 */
define('PLUGINS_REPLACED', 'replaced');
/**
 * Алиасы для классов(при отсутствии функции)
 */
define('CLASS_ALIASES', "include/system/aliases.php");


/**
 * Бесконечность и далее...
 */
define('unended', "&#8734;");
/**
 * Пустая опция
 */
define('empty_option', "<option value='0'>-----</option>");
/**
 * Ошибка неуникальности значения
 */
define('UNIQUE_VALUE_ERROR', 1062);
/**
 * Регэксп для отличного слова
 */
define('WORD_REGEXP', '[\wа-я]');
/**
 * Регэксп для отличного от слова
 */
define('UNWORD_REGEXP', '[^\wа-я]');
/**
 * Паттерн по-умолчанию для создания уникального имени файла
 */
define('DEFAULT_FILENAME_PATTERN', "s%.0f_%de");
/**
 * Сообщение о том, что всё хорошо
 */
define('OK_MESSAGE', 'OK!');

/**
 * Установите 0, если движёк находится не в стадии разработки
 */
define('IN_DEVELOPMENT', 1);



/**
 * Тема по-умолчанию, на случай, если не удалось подключить БД и прочитать из конфигурации
 */
define('DEFAULT_THEME', 'CTRev');
/**
 * Язык по-умолчанию, на случай, если не удалось подключить БД и прочитать из конфигурации
 */
define('DEFAULT_LANG', 'ru');



// Константы для конфига, напр.
/**
 * Рразрешено загружать аватары по URL
 */
define('ALLOWED_AVATAR_URL', '1');
/**
 * Разрешено загружать аватары с компьютера
 */
define('ALLOWED_AVATAR_PC', '2');

/**
 * Разрешено загружать скриншоты по URL
 */
define('ALLOWED_IMG_URL', '1');
/**
 * Разрешено загружать скриншоты с компьютера
 */
define('ALLOWED_IMG_PC', '2');
?>