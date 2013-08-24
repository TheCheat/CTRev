<div id="feedback_container" class='hidden'>
    <form action='javascript:void(0);' onsubmit='send_feedback(this);'>
        <div class="modalbox_title">
            <div class="status_icon" id="feedback_status_icon"></div>
            [*"feedback_title"|lang*]
        </div>
        <div class="modalbox_content white_color">
            <dl class='info_text'>
                <dt>[*'feedback_type'|lang*]</dt>
                <dd>[*select_feedback name='type'*]</dd>
                <dt>[*'feedback_subject'|lang*]</dt>
                <dd><input type='text' name='subject' size='30'></dd>
                <dt>[*'feedback_content'|lang*]</dt>
                <dd>
                    <textarea name='content' rows='5' cols='30'></textarea>
                </dd>
                [*if !""|user*]
                    <dt>[*'feedback_captcha'|lang*]</dt>
                    <dd>[*include file="captcha.tpl"*]</dd>
                [*/if*]
            </dl>
        </div>
        <div class="modalbox_subcontent" align='right'>
            <input type='submit' value='[*'send'|lang*]'>
        </div>
    </form>
</div>