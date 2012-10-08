<div class='margin_auto'>
    <dl class='info_text'>
        <dt><?= lang::o()->v('install_database_dbhost') ?></dt>
        <dd><input type="text" value='<?= $data['dbhost'] ?>' name='dbhost' size="30"></dd>
        <dt><?= lang::o()->v('install_database_dbuser') ?></dt>
        <dd><input type="text" value='<?= $data['dbuser'] ?>' name='dbuser' size="30"></dd>
        <dt><?= lang::o()->v('install_database_dbpass') ?></dt>
        <dd><input type="text" value='<?= $data['dbpass'] ?>' name='dbpass' size="30"></dd>
        <dt><?= lang::o()->v('install_database_dbname') ?></dt>
        <dd><input type="text" value='<?= $data['dbname'] ?>' name='dbname' size="30"></dd>
        <dt><?= lang::o()->v('install_database_charset') ?></dt>
        <dd><input type="text" value='<?= $data['charset'] ?>' name='charset' size="30"></dd>
    </dl>
</div>