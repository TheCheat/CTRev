<?php

/**
 * Project:            	CTRev
 * File:                class.smtp.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @author 	  	Ivan Priorov(http://www.phpclasses.org/package/5032-PHP-Send-e-mail-messages-via-SMTP.html)
 * @name 	        Инициализация функции для отправки E-mail сообщения
 * @version           	1.00
 * @copyright         	(c) 2009-2012, http://www.phpclasses.org
 */
if (!defined('INSITE'))
    die("Remote access denied!");

final class smtp {

    /**
     * Адрес SMTP сервера
     * @var string
     */
    public $smtpServer = 'you.smtp_server.com';

    /**
     * Порт SMTP сервера
     * @var int
     */
    public $port = 25;

    /**
     * Таймаут
     * @var int
     */
    public $timeout = 45;

    /**
     * Имя STMP пользователя
     * @var string
     */
    public $username = 'address@you_domain.com';

    /**
     * Пароль SMTP пользователя
     * @var string
     */
    public $password = 'YouPassword';

    /**
     * Переход на новую линию
     * @var string
     */
    public $newline = "\r\n";

    /**
     * Домен сайта
     * @var string
     */
    public $localdomain = 'you_domain.com';

    /**
     * Кодировка
     * @var string
     */
    public $charset = 'utf-8';

    /**
     * Энкодинг содержимого
     * @var bool
     */
    public $contentTransferEncoding = false;

    /**
     * Окончательная ошибка
     * @var string
     */
    public $endError = '';
    // Промежуточные переменные
    private $smtpConnect = false;
    private $to = false;
    private $subject = false;
    private $message = false;
    private $headers = false;
    private $logArray = array();
    private $Error = '';

    /**
     * Конструктор SMTP отправки сообщения
     * @global lang $lang
     * @param string $to конечный адресат
     * @param string $subject тема сообщения
     * @param string $message сообщение
     * @return bool true, если успешно подключился к серверу
     */
    public function __construct($to, $subject, $message, $params = array()) {
        global $lang;
        $lang->get('smtp');
        if (is_array($params) && $params) {
            foreach ($params as $key => $value)
                $this->$key = $value;
        }
        $this->to = &$to;
        $this->subject = &$subject;
        $this->message = &$message;
        if (!$this->Connect2Server()) {
            $ERROR = $this->Error . $this->newline . '<!-- ' . $this->newline;
            $ERROR .= print_r($this->logArray, true);
            $ERROR .= $this->newline . '-->' . $this->newline;
            return false;
        }
        return true;
    }

    /**
     * Подключение к серверу SMTP и отправка необходимых комманд
     * @global lang $lang
     * @return bool true, в случае удачной отправки
     */
    private function Connect2Server() {
        global $lang;
        $this->smtpConnect = fsockopen($this->smtpServer, $this->port, $errno, $error, $this->timeout);
        $this->logArray ['CONNECT_RESPONSE'] = $this->readResponse();

        if (!is_resource($this->smtpConnect)) {
            return false;
        }
        $this->logArray ['connection'] = $lang->v('smtp_conn_accept') . "$smtpResponse";
        $this->sendCommand("EHLO $this->localdomain");
        $this->logArray ['EHLO'] = $this->readResponse();
        $this->sendCommand('AUTH LOGIN');
        $this->logArray ['AUTH_REQUEST'] = $this->readResponse();
        $this->sendCommand(base64_encode($this->username));
        $this->logArray ['REQUEST_USER'] = $this->readResponse();
        $this->sendCommand(base64_encode($this->password));
        $this->logArray ['REQUEST_PASSWD'] = $this->readResponse();
        if (substr($this->logArray ['REQUEST_PASSWD'], 0, 3) != '235') {
            $this->Error .= $lang->v('smtp_error_auth') . $this->logArray ['REQUEST_PASSWD'] . $this->newline;
            return false;
        }
        $this->sendCommand("MAIL FROM: $this->username");
        $this->logArray ['MAIL_FROM_RESPONSE'] = $this->readResponse();
        if (substr($this->logArray ['MAIL_FROM_RESPONSE'], 0, 3) != '250') {
            $this->Error .= $lang->v('smtp_error_send_addr') . $this->logArray ['MAIL_FROM_RESPONSE'] . $this->newline;
            return false;
        }
        $this->sendCommand("RCPT TO: $this->to");
        $this->logArray ['RCPT_TO_RESPONCE'] = $this->readResponse();
        if (substr($this->logArray ['RCPT_TO_RESPONCE'], 0, 3) != '250') {
            $this->Error .= $lang->v('smtp_error_rec_addr') . $this->logArray ['RCPT_TO_RESPONCE'] . $this->newline;
        }
        $this->sendCommand('DATA');
        $this->logArray ['DATA_RESPONSE'] = $this->readResponse();
        if (!$this->sendMail())
            return false;
        $this->sendCommand('QUIT');
        $this->logArray ['QUIT_RESPONSE'] = $this->readResponse();
        fclose($this->smtpConnect);
        return true;
    }

    /**
     * Непосредственно, отправка сообщения
     * @global lang $lang
     * @return bool true, в случае удачной отправки
     */
    private function sendMail() {
        global $lang;
        $this->sendHeaders();
        $this->sendCommand($this->message);
        $this->sendCommand('.');
        $this->logArray ['SEND_DATA_RESPONSE'] = $this->readResponse();
        if (substr($this->logArray ['SEND_DATA_RESPONSE'], 0, 3) != '250') {
            $this->Error .= $lang->v('smtp_error_send_data') . $this->logArray ['SEND_DATA_RESPONSE'] . $this->newline;
            return false;
        }
        return true;
    }

    /**
     * Чтение ответа
     * @return string ответ сервера
     */
    private function readResponse() {
        $data = "";
        while ($str = fgets($this->smtpConnect, 4096)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    }

    /**
     * Отправка комманды серверу
     * @return null
     */
    private function sendCommand($string) {
        fputs($this->smtpConnect, $string . $this->newline);
        return;
    }

    /**
     * Отправка хеадеров
     * @return null
     */
    private function sendHeaders() {
        $this->sendCommand("Date: " . date("D, j M Y G:i:s") . " +0300");
        $this->sendCommand("From: <$this->username>");
        $this->sendCommand("Reply-To: <$this->username>");
        $this->sendCommand("To: <$this->to>");
        $this->sendCommand("Subject: $this->subject");
        $this->sendCommand("MIME-Version: 1.0");
        $this->sendCommand("Content-Type: text/html; charset=$this->charset");
        if ($this->contentTransferEncoding)
            $this->sendCommand("Content-Transfer-Encoding: $this->contentTransferEncoding");
        $this->sendCommand($this->newline);
        return;
    }

    /**
     * Деструктор SMTP
     * @return null
     */
    public function __destruct() {
        if (is_resource($this->smtpConnect))
            fclose($this->smtpConnect);
    }

}

?>