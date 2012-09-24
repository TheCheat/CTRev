<?php

$languages = array(
    'install_title' => 'Установка CTRev',
    'install_language_selector' => 'Язык / Language',
    'install_page_license' => "Лицензионное соглашение",
    'install_page_check' => 'Проверка CHMOD',
    'install_page_database' => 'Настройка БД',
    'install_page_import' => 'Импорт БД',
    'install_page_admin' => 'Создание администратора',
    'install_page_config' => 'Конфигурация сайта',
    'install_page_finish' => 'Завершение установки',
    'install_license' => 'LICENSE_RU',
    'install_continue' => 'Продолжить',
    "install_back" => 'Назад',
    'install_check_necessary_files' => 'Необходимые дирректории и файлы',
    'install_check_rewritable' => 'перезаписываем',
    'install_check_writable_yes' => '<font color="green">полностью</font>',
    'install_check_writable_part' => '<font color="red">частично</font>',
    'install_check_writable_no' => '<font color="red">не</font>',
    'install_check_templates' => '<span><abbr title="themes/{имя шаблона}/templates">Шаблоны</abbr></span> %s перезаписываемы(необх. для плагинов)',
    'install_check_lang' => '<span><abbr title="languages/{имя пакета}">Языковые пакеты</abbr></span> %s перезаписываемы(необяз.)',
    'install_check_notice' => '*Причмечание: Файл install/lock отсутсвует и не нужно его создавать. 
        Если написано, что он неперезаписываем, то это означает, что невозможна запись в папку install',
    'install_check_env' => 'Сервер',
    'install_check_upload_filesize' => '<abbr title="Поля upload_max_filesize и post_max_size 
        в конфигурации PHP(php.ini)">Макс. размер загружаемых файлов</abbr>',
    'install_check_filesize_recomm' => 'мин. <b>%d</b> мегабайт, рек. не менее <b>15</b> мегабайт',
    'install_check_php' => 'PHP(мин. 5.0, рек. 5.3): ',
    'install_check_mbstring' => 'Поддержка mbstring? ',
    'install_check_furl' => 'Поддержка ЧПУ?(необяз.) ',
    'install_check_curl' => 'Поддержка CURL?(необяз.) ',
    'install_check_notice' => '<b>*Примечание: install/lock</b> - файл, который создаётся после инсталляции. <br>
        Если невозможно его записать, значит папка <b>install</b> не обладает необходимыми правами.',
    'install_database_dbhost' => 'Хост БД(:порт): ',
    'install_database_dbuser' => 'Пользователь БД: ',
    'install_database_dbpass' => 'Пароль БД: ',
    'install_database_dbname' => 'Имя БД: ',
    'install_database_charset' => 'Кодировка БД: ',
    'install_import_query' => 'Запрос на %s <b>%s</b> прошёл %s<br>',
    'install_import_query_typec' => 'добавление таблицы',
    'install_import_query_typea' => 'изменение таблицы',
    'install_import_query_typer' => 'замену данных в таблице',
    'install_import_query_typeu' => 'обновление данных в таблице',
    'install_import_query_typei' => 'вставку в таблицу',
    'install_import_query_success' => '<font color="green"><b>успешно</b></font>',
    'install_import_query_error' => '<font color="red"><b>с ошибкой(%d)</b></font>',
    'install_admin_username' => 'Имя пользователя',
    'install_admin_password' => 'Пароль',
    'install_admin_passagain' => 'Повтор пароля',
    'install_admin_email' => 'E-mail',
    'install_config_site_title' => 'Заголовок сайта',
    'install_config_site_path' => 'Путь к корню сайта',
    'install_config_email' => 'E-mail для связи',
    'install_config_furl' => 'Включить ЧПУ?',
    'install_config_cache' => 'Включить кеш?',
    'install_finish' => 'Установка успешно завершена, теперь Вы можете 
        использовать движок в тех целях, для которых он предназначен. <br><br>
        Инсталляция заблокирована. Чтобы произвести повторную установку движка,
        удалите файл %s.<br><br>
        <font color="red"><b>Примечание:</b></font> при редактировании
        профиля в <b>"Панели Управления"</b> Вам необходимо заполнить все поля, 
        отмеченные звёздочкой(напр. пол, дата рождения)!<div class="br"></div>
        <center><font size="3"><a href="./"><b>На главную</b></a></font></center>',
    'install_error_not_rewritable' => 'Дирректория/файл %s не перезаписываемы',
    'install_error_cant_write_dbconn' => 'Невозможно записать файл для коннекта с БД',
    'install_error_php_version' => 'Версия PHP ниже необходимой',
    'install_error_mbstring_non_exists' => 'Расширение mbstring отсуствует',
    'install_error_upload_filesize' => 'Допустимый размер загружаемых файлов слишком мал!',
    'install_error_table_non_exists' => 'Таблица <b>%s</b> отсуствует',
    'install_error_passwords_not_match' => 'Пароль не совпадает с повтором пароля',
    'install_error_wrong_username' => 'Неправильное имя пользователя',
    'install_error_wrong_password' => 'Неправильный пароль',
    'install_error_wrong_email' => 'E-mail не похож на настоящий');
?>