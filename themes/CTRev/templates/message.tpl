<div align="left" class='m_message'>
    [*if $title*]
        <div class="[*$type*]_title" [*if $align*] align="[*$align*]"[*/if*]>[*$title*]</div>
    [*/if*]
    <div class="[*$type*] m_message_table">
        <div [*if $align*] align="[*$align*]"[*/if*] class='content'>
            <div class='tr'>
                [*if !$no_image*]
                    <div class="m_message_image [*$type*]_image td"></div>
                [*/if*]
                <div class='td'>
                    <div class='m_message_content' id="[*$type*]_message">
                        [*$message*]<br>
                        [*if $died_mess*]
                            <font size="1"><a href="javascript:history.go(-1);">[*'back'|lang*]</a></font>
                        [*/if*]
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>