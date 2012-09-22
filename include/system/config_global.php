<?php

if (!defined('INSITE'))
    die('Remote access denied!');

define('ENGINE_VERSION', '1.00');
define('ENGINE_STAGE', 'RC1');

define('MODULES_PATH', 'modules'); // путь к модулям, относительно корня системы
define('BLOCKS_PATH', 'blocks'); // путь к блокам, относительно модулей
define('ADMIN_MODULES_PATH', 'admincp/modules'); // путь к модулям АЦ, относительно корня системы
define('ADMIN_PAGES_PATH', 'admincp/pages'); // путь к старинцам АЦ, относительно корня системы
define('THEMES_PATH', 'themes'); // путь к темам, относительно корня системы
define('TEMPLATES_PATH', 'templates'); // путь к шаблонам, относительно темы
define('LANGUAGES_PATH', 'languages'); // путь к языковым пакетам, относительно корня системы
define('PLUGINS_PATH', 'include/plugins'); // путь к плагинам, относительно корня системы
define('PLUGINS_INC', 'inc'); // путь к инклуд-файлам плагинов, относительно пути к плагинам
define('PLUGINS_REPLACED', 'replaced'); // путь к логам заменённых выражений, относительно пути к плагинам
define('CLASS_ALIASES', "include/system/aliases.php"); // алиасы для классов(при отсутствии функции)

define('unended', "&#8734;"); // бесконечность и далее...
define('empty_option', "<option value='0'>-----</option>"); // пустая опция
define('UNIQUE_VALUE_ERROR', 1062); // Ошибка неуникальности значения

define('IN_DEVELOPMENT', 1); // Установите 0, если движёк находится не в стадии разработки
define('XSS_PROTECT', 1); // НЕ ТРОГАТЬ, БЛДЖАД!

define('DEFAULT_THEME', 'CTRev'); // Тема по-умолчанию, на случай, если не удалось подключить БД и прочитать из конфигурации
define('DEFAULT_LANG', 'ru'); // Язык по-умолчанию, на случай, если не удалось подключить БД и прочитать из конфигурации

// Константы для конфига, напр.
define('ALLOWED_AVATAR_URL', '1');
define('ALLOWED_AVATAR_PC', '2');

define('ALLOWED_IMG_URL', '1');
define('ALLOWED_IMG_PC', '2');
?>