<?php

if (!defined("INSITE"))
    die("Remote access denied!");

class plugin_recaptcha {

    /**
     * Версия плагина
     * @var string $version
     */
    public $version = '1.00';

    /**
     * Имя плагина
     * @var string $name
     */
    public $name = 'reCAPTCHA integration';

    /**
     * Описание плагина
     * @var string $descr
     */
    public $descr = 'Интеграция reCAPTCHA вместо стандартной капчи';

    /**
     * Совместимость с движком. Наилучшая
     * @var string $compatibility
     */
    public $compatibility = '1.00';

    /**
     * Совместимость с движком.  Мин. версия.
     * @var string $compatibility_min
     */
    public $compatibility_min = '1.00';

    /**
     * Совместимость с движком. Макс. версия.
     * @var string $compatibility_max
     */
    public $compatibility_max = '1.00';

    /**
     * Автор плагина
     * @var string $author
     */
    public $author = 'The Cheat';

    /**
     * Инициализация плагина
     * @param plugins $plugins объект плагиновой системы
     * @return null
     * @note Здесь настраивается, какие классы плагин переопределяет, 
     * расширяет, какие хуки задействованы.
     */
    public function init($plugins) {
        $plugins->redefine_class("captcha", "recaptcha");
        lang::o()->bind('admin/config', 'plugins/recaptcha');
        tpl::o()->register_function('recaptcha_show', array("recaptcha", "show"));
    }

    /**
     * Установка плагина
     * @param bool $re переустановка?
     * в данном случае необходимо лишь произвести изменения в файлах
     * @return bool статус установки
     * @note метод может возвращать false или 0, в случае, если была какая-то
     * критическая ошибка при удалении
     */
    public function install($re = false) {
        if (!$re) {
            config::o()->add('recaptcha_public_key', '', 'string', '', 'register');
            config::o()->add('recaptcha_private_key', '', 'string', '', 'register');
        }
        plugins::o()->insert_template('captcha.tpl', '[*recaptcha_show*][**');
        plugins::o()->insert_template('captcha.tpl', '**]', false);
        return true;
    }

    /**
     * Удаление плагина
     * @param bool $replaced было ли успешно ВСЁ замененённое сохранено?
     * @return bool статус удаления
     * @note метод может возвращать false или 0, в случае, если была какая-то
     * критическая ошибка при удалении
     */
    public function uninstall($replaced = false) {
        config::o()->remove('recaptcha_public_key');
        config::o()->remove('recaptcha_private_key');
        return $replaced;
    }

}

?>