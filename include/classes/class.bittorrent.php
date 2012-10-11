<?php

/**
 * Project:            	CTRev
 * File:                class.bittorrent.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс для работы с торрент-файлами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class announce_parser extends fbenc {

    /**
     * Проверка URL-а аннонсера и возвращение частей URL-а
     * @param string $url URL аннонсера
     * @return array Matches из preg_match
     * 2 - протокол
     * 3 - домен
     * 4 - порт
     * 5 - оставшаяся часть
     */
    protected function check_announce($url) {
        preg_match('/^' . display::url_pattern . '$/siu', $url, $m);
        if ($m[2] == "ftp" || $m[3] == "retracker.local")
            return;
        return $m;
    }

    /**
     * Получение списка аннонсеров из торрента
     * @param array $dict словарь торрента
     * @return array список аннонсеров
     */
    public function announce_lists(&$dict) {
        $announce_urls = array();
        if (config::o()->v('multitracker_on')) {
            if ($dict ['announce-list']) {
                if (!is_array($dict['announce-list']))
                    $dict ['announce-list'] = array(array($dict ['announce-list']));
                foreach ($dict ['announce-list'] as $k => $tv) {
                    if (!is_array($tv)) {
                        unset($dict ['announce-list'][$k]);
                        continue;
                    }
                    foreach ($tv as $t)
                        if ($t && $this->check_announce($t))
                            $announce_urls [] = $t;
                }
            } elseif ($dict ['announce'])
                $announce_urls [] = $dict ['announce'];
        } else
            unset($dict ['announce-list']);
        $announce_urls = array_unique($announce_urls);
        return $announce_urls;
    }

}

class bittorrent extends announce_parser {
    /**
     * Паттерн для именования файлов в зависимости от времени загрузки
     */

    const files_pattern = 's%d_%de';
    /**
     * Префикс в имени торрент файла, хранимого на сервере
     */
    const torrent_prefix = "t";

    /**
     * Макс. кол-во файлов, записываемых в БД
     */
    const max_filelist = 100;

    /**
     * Получение значащей части имени файла, уникальный идентефикатор для каждого торрента
     * @param int $time время создания
     * @param int $poster_id ID создателя
     * @return string идентефикатор
     */
    public static function get_filename($time, $poster_id) {
        $poster_id = (int) $poster_id;
        $time = (int) $time;
        return sprintf(self::files_pattern, $time, $poster_id);
    }

    /**
     * Замена passkey в URL трекера на настройки пользователя
     * @param string $url URL трекера
     * @param string|array $pk строка из конфига
     * @return bool true, в случае успешной замены
     */
    protected function pk_replace(&$url, &$pk) {
        if (!is_array($pk))
            $pk = preg_split('/\s+/', trim($pk));
        list($host, $pk_s, $pk_e) = $pk;
        if (!$host || !$pk_s)
            return;
        if (!preg_match('/^' . display::url_pattern . '$/siu', $url, $m))
            return;
        if (mb_strpos($m[3] . ':' . $m[4], $host) === false)
            return;
        $apk = users::o()->v('announce_pk');
        $url = preg_replace('/(' . mpc($pk_s) . ')(.*?)(' . ($pk_e ? mpc($pk_e) : "$") . ')/siu', '$1' .
                $apk[$host] . '$3', $url);
        return true;
    }

    /**
     * В списке аннонсеров?
     * @param string $url URL аннонсера
     * @param array $list список
     * @return bool true, если в списке
     */
    protected function in_annlist($url, $list) {
        foreach ($list as $i)
            if (in_array($url, $i))
                return true;
        return false;
    }

    /**
     * Предобработка dict перед скачиванием
     * @param int $id ID торрента
     * @param int $posted_time время постинга
     * @param int $poster_id ID автора
     * @return array словарь торрента
     */
    protected function prepare_dict($id, $posted_time, $poster_id) {

        $fname = self::get_filename($posted_time, $poster_id);
        $dict = $this->bdec(ROOT . config::o()->v('torrents_folder') . '/' . self::torrent_prefix . $fname . ".torrent", true);

        $dict ['comment'] = sprintf(lang::o()->v('torrents_from_site'), config::o()->v('site_title'), furl::o()->construct("download", array(
                    "id" => $id,
                    'noencode' => true)));

        $passkey = users::o()->v('passkey');
        $dict ['announce'] = config::o()->v('annadress') ? config::o()->v('annadress') :
                furl::o()->construct('announce', array(
                    'passkey' => $passkey,
                    'noencode' => true), false, true);
        if (!is_array($dict ['announce-list']))
            unset($dict ['announce-list']);
        if (config::o()->v('get_pk') && $dict ['announce-list']) {
            if (users::o()->v('settings'))
                users::o()->decode_settings();
            if (!is_array(users::o()->v('announce_pk')))
                users::o()->unserialize('announce_pk');
            $pk = explode("\n", config::o()->v('get_pk'));
            $c = count($pk);
            foreach ($dict ['announce-list'] as $k => $a)
                foreach ($a as $l => $b)
                    for ($i = 0; $i < $c; $i++)
                        if ($this->pk_replace($dict ['announce-list'][$k][$l], $pk[$i]))
                            break;
        }
        if (config::o()->v('additional_announces')) {
            $add = explode("\n", config::o()->v('additional_announces'));
            foreach ($add as $a) {
                $a = trim($a);
                if ($this->in_annlist($a, $dict ['announce-list']))
                    continue;
                $a = array($a);
                $dict ['announce-list'][] = $a;
            }
        }
        if ($dict ['announce-list'])
            array_unshift($dict ['announce-list'], array($dict ['announce']));
        return $dict;
    }

    /**
     * Скачивание торрент файла
     * @param int $id ID торрент файла
     * @return null
     * @throws EngineException 
     */
    public function download_torrent($id) {

        $id = (int) $id;
        try {
            users::o()->check_perms('torrents');
            $r = db::o()->query('SELECT t.banned, t.poster_id, t.posted_time, t.price, d.uid FROM torrents AS t
            LEFT JOIN downloaded AS d ON d.tid=t.id AND d.uid=' . users::o()->v('id') . '
            WHERE t.id=' . $id . ' LIMIT 1');
            list($banned, $poster_id, $posted_time, $price, $downloaded) = db::o()->fetch_row($r);
            lang::o()->get('torrents');
            if (!$poster_id || $banned)
                throw new EngineException('torrents_no_this_torrents');
            $off = users::o()->perm('free', 2) ? 1 : (users::o()->perm('free', 1) ? 0.5 : 0);
            $price = $price * (1 - $off);

            if ($poster_id == users::o()->v('id'))
                $downloaded = true;

            plugins::o()->pass_data(array('price' => &$price,
                'id' => $id), true)->run_hook('torrents_download_price');

            if (users::o()->v('bonus_count') < $price)
                throw new EngineException('torrents_no_enough_bonus');
            /* @var $etc etc */
            $etc = n("etc");
            if (!$downloaded) {
                if ($price)
                    $etc->add_res('bonus', -$price);
                db::o()->insert(array('tid' => $id, 'uid' => users::o()->v('id')), 'downloaded');
            }
            $dict = $this->prepare_dict($id, $posted_time, $poster_id);

            plugins::o()->pass_data(array('dict' => &$dict))->run_hook('torrents_download_dict');

            $name = 'id' . $id . '[' . $_SERVER["HTTP_HOST"] . '].torrent';
            /* @var $uploader uploader */
            $uploader = n("uploader");
            $uploader->download_headers($this->benc($dict), $name, "application/x-bittorrent");
        } catch (PReturn $e) {
            return $e->r();
        }
    }

    /**
     * Проверка dict перед записью
     * @param string $t путь к файлу торрента
     * @param array $filelist список файлов
     * @param int $filesize размер файла
     * @param array $announce_list список аннонсеров
     * @return array массив из словаря и раздела info словаря
     * @throws EngineException 
     */
    protected function check_dict($t, &$filelist = null, &$filesize = null, &$announce_list = null) {
        $dict = $this->bdec($t, true);
        if (!$dict)
            throw new EngineException('bencode_cant_parse_file');
        list($info) = $this->dict_check($dict, "info");
        list($dname, $plen, $pieces,
                $tlen, $flist) = $this->dict_check($info, "name(s):piece length(i):pieces(s):!length(i):!files(l)");
        if (strlen($pieces) % 20 != 0)
            throw new EngineException("bencode_invalid_key");
        if ($tlen) {
            $filelist [] = array($dname, $tlen);
            $filesize = $tlen;
        } else {
            if (!$flist || !is_array($flist) || count($flist) < 1)
                throw new EngineException("bencode_dict_miss_keys");
            $filesize = 0;
            $i = 0;
            foreach ($flist as $fn) {
                $i++;
                if ($i > self::max_filelist) {
                    $filelist [] = array(
                        "...",
                        0);
                    break;
                }
                list ( $ll, $ff ) = $this->dict_check($fn, "length(i):path(l)");
                $filesize += $ll;
                $ffa = array();
                foreach ($ff as $ffe) {
                    if (!is_string($ffe))
                        throw new EngineException("bencode_wrong_filename");
                    $ffa [] = $ffe;
                }
                if (!count($ffa))
                    throw new EngineException("bencode_wrong_filename");
                $ffe = implode("/", $ffa);
                $filelist [] = array(
                    $ffe,
                    $ll);
            }
        }
        $filelist = serialize($filelist);
        $idict = &$dict ['info'];
        if (config::o()->v('DHT_on') == 0)
            $idict ['private'] = 1;
        elseif (config::o()->v('DHT_on') == 1)
            unset($idict ['private']);
        // не меняем, если -1

        $announce_list = $this->announce_lists($dict);
        $announce_list = serialize($announce_list);

        // удаляем излишки
        unset($dict ['nodes']);
        unset($idict ['crc32']);
        unset($idict ['ed2k']);
        unset($idict ['md5sum']);
        unset($idict ['sha1']);
        unset($idict ['tiger']);
        unset($dict ['azureus_properties']);

        $dict ['publisher.utf-8'] = $dict ['publisher'] = $dict ['created by'] = users::o()->v('username');
        $dict ['publisher-url.utf-8'] = $dict ['publisher-url'] = furl::o()->construct("users", array(
            "user" => users::o()->v('username'),
            'noencode' => true));
        return array($dict, $idict);
    }

    /**
     * Проверка и загрузка торрент-файла
     * @param string $id ID торрент-файла
     * @param string $filevar файловая переменная($_FILES) для торрента
     * @param string $filelist ссылка на сериализованный список файлов в торренте
     * @param int $filesize ссылка на размер файла
     * @param string $announce_list ссылка на аннонсеры для мультитрекера
     * @return string инфохеш торрент файла
     */
    public function torrent_file($id, $filevar, &$filelist = null, &$filesize = null, &$announce_list = null) {
        n("uploader")->check($filevar, /* ссылка */ $tmp = 'torrents');
        $t = $filevar["tmp_name"];

        list($dict, $idict) = $this->check_dict($t, $filelist, $filesize, $announce_list);

        file::o()->write_file($this->benc($dict), config::o()->v('torrents_folder') . '/' . self::torrent_prefix . $id . ".torrent");

        return sha1($this->benc($idict));
    }

}

?>