<dl class="info_text">
    <dt><?= $lang->v('install_check_necessary_files') ?></dt>
    <dd><ul class='check_rewritable'>
            <?php
            foreach ($data['chmod'] as $f) {
                $c = $data['file']->is_writable($f, true, true, true);
                $s = $data['this']->rewritable($c);
                print('<li><span>' . $f . '</span> ' . $s . ' ' . $lang->v('install_check_rewritable') . '</li>');
            }
            $c = $data['this']->check_dir();
            $c2 = $data['this']->check_dir('languages');
            $s = $data['this']->rewritable($c);
            $s2 = $data['this']->rewritable($c2);
            ?>
            <li><?= sprintf($lang->v('install_check_templates'), $s) ?></li>
            <li><?= sprintf($lang->v('install_check_lang'), $s2) ?></li>
        </ul>
        <font color="1"><?= $lang->v('install_check_notice') ?></font>
    </dd>
    <dt><?= $lang->v('install_check_env') ?></dt>
    <dd><ul class='check_rewritable'>
            <li>
                <span><?= $lang->v('install_check_php') ?></span>
                <?= $data['this']->colored(version_compare(PHP_VERSION, '5.0', '>='), PHP_VERSION) ?>
            </li>
            <li>
                <span><?= $lang->v('install_check_mbstring') ?></span>
                <?= $data['this']->colored(in_array('mbstring', get_loaded_extensions())) ?>
            </li>
            <li>
                <span><?= $lang->v('install_check_furl') ?></span>
                <?= $data['this']->colored($_SERVER['HTTP_FURL_AVALIABLE']) ?>
            </li>
            <li>
                <span><?= $lang->v('install_check_curl') ?></span>
                <?= $data['this']->colored(in_array('curl', get_loaded_extensions())) ?>
            </li>
        </ul>
    </dd>
</dl>
<center><font size='1'><?= $lang->v('install_check_notice') ?></font></center>