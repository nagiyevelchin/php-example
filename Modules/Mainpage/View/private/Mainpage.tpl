<script type="text/javascript" src="{$config.sitedomain}jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="{$config.sitedomain}jscripts/init_mce.js?v=4"></script>
<script type="text/javascript" src="{$config.sitedomain}Modules/Mainpage/View/private/Mainpage.js"></script>
<form action="" method="POST" name='myform' enctype="multipart/form-data">
    <table>
        {foreach from=$grow.site_langs item=l key=lid}
            <tr>
                <td valign='top' align="right">{$l.name}</td>
                <td>
                    <textarea name="Mainpage[{$l.id}][text]" placeholder="desc" id="edesc_{$lid}" class="ui-widget ui-widget-content ui-corner-all px200 htmleditor" cols="30" rows="10">{$post.Mainpage[$l.id].text}</textarea>
                    <div class="radioset" style="float: right; padding: 5px;">
                        <input type="radio" checked="checked" name="r_desc_{$lid}" tiny_id="edesc_{$lid}" class="tiny_destroy" id="r_desc_{$lid}_yes"/><label for="r_desc_{$lid}_yes">{'Plain'|lang:$smarty.current_dir:$smarty.template}</label>
                        <input type="radio" name="r_desc_{$lid}" tiny_id="edesc_{$lid}" class="tiny_init" id="r_desc_{$lid}_no"/><label for="r_desc_{$lid}_no">{'Editor'|lang:$smarty.current_dir:$smarty.template}</label>
                    </div>
                </td>
            </tr>
        {/foreach}
    </table>
    <div id="savediv">
        <input type="submit" class="button" value="{'Yadda saxla'|lang:$smarty.current_dir:$smarty.template}"/>
    </div>
    <input type="hidden" name="action" value="save"/>
</form>
