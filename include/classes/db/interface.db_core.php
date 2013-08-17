<?php

/**
 * Project:            	CTRev
 * @file                include/classes/interface.captcha.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Интерфейс для ядра БД
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

interface db_core_interface {

    /**
     * Коннект к БД
     * @return null
     */
    public function connect();

    /**
     * Последний вставленный ID
     * @param resid $res ID запроса
     * @return int ID новой строки
     */
    public function insert_id($res = null);

    /**
     * Выполнение запроса к БД
     * @param string $query строка запроса
     * @return resid ID запроса
     */
    public function query($query);

    /**
     * Получение строки результата запроса (row)
     * @param resid $result ID запроса
     * @return array массив, где ключи - целые числа
     */
    public function fetch_row($result);

    /**
     * Получение строки результата запроса (assoc)
     * @param resid $result ID запроса
     * @return array массив, где ключи - столбцы, заданные в запросе
     */
    public function fetch_assoc($result);

    /**
     * Получение кол-ва строк результата запроса
     * @param resid $result ID запроса
     * @return int кол-во строк результата
     */
    public function num_rows($result);

    /**
     * Проверка сколько строк было изменено
     * @param resid $link_identifier ID запроса
     * @return int изменённые строки
     */
    public function affected_rows($link_identifier = null);

    /**
     * Получение текста ошибки
     * @return string текст ошибки
     */
    public function errtext();

    /**
     * Возвращает номер ошибки запроса
     * @return int номер ошибки
     */
    public function errno();

    /**
     * Экранирование строки в условии запроса
     * @param string $value входное значение
     * @return string экранированное значение
     */
    public function esc($value);

    /**
     * Экранирование строки для использование в LIKE
     * @param string $x входное значение
     * @return string экранированное значение
     */
    public function sesc($x);

    /**
     * Версия MySQL
     * @return string версия 
     */
    public function version();

    /**
     * Создание условия для полнотекстового поиска
     * @param string $value искомые слова
     * @param string|array $columns столбец/столбцы
     * @param bool $boolean поиск в логическом режиме
     * @return string условие
     */
    public function fulltext_search($columns, $value, $boolean);
}

?>