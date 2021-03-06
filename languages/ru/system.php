<?php

$languages = array(
    "lang_ru" => "Русский",
    "lang_en" => "English",
    "file" => "Файл",
    "line" => "Линия",
    "module_not_exists" => "Данный модуль не сущеcтвует!",
    "cannot_write_cache" => "Не могу сохранить кеш страницы ",
    "db_error" => "Ошибка MySQL",
    "db_parse_not_enough_params" => 'Недостаточно параметров для парсинга!(Запрос: %s)',
    "db_chronology" => "Бэктрейс: ",
    "you_can_contact_admin" => "Вы можете уведомить об ошибке администратора по E-mail: ",
    'unauthorized_ip' => 'Ваш IP запрещён администрацией сайта!',
    "reason" => "Причина: ",
    'no_guest_group' => 'Группа гостя не найдена в БД! Она может быть восстановлена из дампа установочной БД.',
    'group_doesnt_exists' => 'Данная группа отсутствует в БД! WTF?',
    'invalid_cookie' => 'ID пользователя или Passhash в Cookies неверный. Пожалуйста, почистите ваши Cookies, если данная ошибка повторится.',
    'your_acc_disabled' => 'Ваш аккаунт отключен!',
    "bad_check_key" => "Ключ защиты не совпадает",
    "access_denied" => "Доступ запрещен.",
    "now_site_offline" => "В данный момент сайт отключен",
    "function_was_disabled_by_admin" => "Данная функция была отключена администратором сайта",
    "need_to_upgrade_database" => "<font size='3'>Версия Вашей БД устарела!(необходимо - <u>%s</u>, текущая - <u>%s</u>)</font><br>
        Для обновления необходимо(прочитайте внимательно до конца!):
        <ul>
        <li><a href='install.php'>Установить движок</a>, предварительно сохранив 
        текущие данные в базе, желательно под другим именем.</li>
        <li>Переименовать файл в корне движка из <i>convert.php_</i> в <a href='convert.php'><i>convert.php</i></a></li>
        <li>Восстановить данные, используя таблицы конвертации(выбираются при конвертации) <u>update</u></li>
        <li>Можно обратно переименовать файл <i>convert.php</i> в <i>convert.php_</i>, но это необязательно</li>
        </ul>",
);
?>