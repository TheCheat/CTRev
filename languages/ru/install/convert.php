<?php

$languages = array(
    "install_title" => 'Конвертирование БД',
    'install_page_notice' => 'Инструкция',
    'install_page_database' => 'Настройка конвертации',
    'install_page_convert' => 'Конвертация данных',
    'install_page_finish' => 'Завершение конвертации',
    "convert_select_error" => '<font color="red"><b>Ошибка</b></font> в выборке таблицы "%s": %d(%s)!',
    "convert_insert_error" => '<font color="red"><b>Ошибка</b></font> во вставке таблицы "%s": %d(%s)!',
    'convert_inserted_table' => '%d-%d записи <font color="green"><b>успешно</b></font> конвертированы в "%s" из "%s"<br>',
    'convert_notice' => 'Спасибо за установку CTRev! Сейчас Вам будет предоставлена возмоность
        перенести данные из других движков на данный. Для этого Вам необходимо выполнить следующие действия:
        <ul>
            <li>Разрешить данному пользователю БД доступ на SELECT из конвертируемой БД</li>
            <li>Скопировать аватары пользователей в папку <b>%s</b></li>
            <li>Скопировать торренты в папку <b>%s</b></li>
            <li>Скопировать скриншоты и постеры в папку <b>%s</b></li>
            <li>Установить права на запись для данных файлов</li>
        </ul>',
    'convert_wrong_db' => 'Невозможно найти данную БД(%s)',
    'convert_cfile_not_exists' => 'Невозможно найти такой файл конвертации(%s)',
    'convert_database_name' => 'Имя конвертируемой БД',
    'convert_database_cfile' => 'Таблица конвертации',
    'convert_database_peronce' => 'Кол-во конвертируемых записей за раз',
    'convert_truncated_tables' => '%d таблиц CTRev <font color="green"><b>успешно</b></font> очищено<br>',
    'convert_plugin_installed' => 'Плагин <font color="green"><b>успешно</b></font> установлен',
    "convert_finished" => 'Конвертация данных успешно завершена!<br>
        Файл заблокирован, чтобы снова запустить конвертацию, удалите из Вашей БД
        таблицу <b>convert</b>.',
    'convert_cant_find_group' => 'Не найдено сопоставление для группы c ID %d',
    'convert_groups_compare' => 'Сопоставление групп',
    'convert_groups_compare_notice' => '<b>*Инструкция:</b> укажите соответствие между
        ID групп конвертируемой БД и групп CTRev. Для того, чтобы указать несколько групп,
        разделяйте их символом <b>"|"</b>.'
);
?>