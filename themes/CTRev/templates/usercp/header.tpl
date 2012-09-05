<div class="cornerText gray_color">
    <fieldset><legend>
            <nobr>
                [*assign var="be" value=0*]
                [*foreach from=$menuacts item=menu*]
                    [*if $be*] 
                        &bull; 
                    [*/if*]
                    <span class="menu_ucp[*if $curact==$menu*] menu_ucp_selected[*/if*]">
                        <a href="[*gen_link module='usercp' act=$menu*]">[*"usercp_$menu"|lang*]</a>
                    </span>
                    [*assign var="be" value=1*]
                [*/foreach*]
            </nobr>
        </legend>
        <center>