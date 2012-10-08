<div class='margin_auto'>
    <dl class='info_text'>
        <dt><?= lang::o()->v('install_config_site_title') ?></dt>
        <dd><input type='text' name='site_title' value='CTRev: A bit of (R)evolution' size='35'></dd>
        <dt><?= lang::o()->v('install_config_site_path') ?></dt>
        <dd><input type='text' name='baseurl' value='<?= $data['baseurl'] ?>' size='35'></dd>
        <dt><?= lang::o()->v('install_config_email') ?></dt>
        <dd><input type='text' name='contact_email' value='admin@<?= $_SERVER['SERVER_NAME'] ?>' size='35'></dd>
        <dt><?= lang::o()->v('install_config_furl') ?></dt>
        <dd><input type='radio' name='furl' value='1'
                   <?= $_SERVER['HTTP_FURL_AVALIABLE'] ? "checked='checked'" : "" ?>>&nbsp;<?= lang::o()->v('yes_simple') ?>
            <input type='radio' name='furl' value='0'
                   <?= !$_SERVER['HTTP_FURL_AVALIABLE'] ? "checked='checked'" : "" ?>>&nbsp;<?= lang::o()->v('no_simple') ?></dd>
        <dt><?= lang::o()->v('install_config_cache') ?></dt>
        <dd><input type='radio' name='cache_on' value='1' checked='checked'>&nbsp;<?= lang::o()->v('yes_simple') ?>
            <input type='radio' name='cache_on' value='0'>&nbsp;<?= lang::o()->v('no_simple') ?>
        </dd>
    </dl>
</div>