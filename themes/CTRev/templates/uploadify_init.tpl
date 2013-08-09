<script type="text/javascript"
src="js/uploader/swfobject.js"></script>
<script type="text/javascript"
src="js/uploader/jquery.uploadify.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $("#uploadify_[*$postfix*]").uploadify({
            'uploader'       : 'js/uploader/uploadify.swf',
            'script'         : '[*$baseurl|sl*]index.php',
            'scriptData'	 : {[*$scriptData*]},
            'method'		 : 'GET',
            'cancelImg'      : '[*$theme_path|sl*]engine_images/cancel.png',
            'queueID'        : 'fileQueue_[*$postfix*]',
            //		[*if $auto*]
		
            'auto'           : true,
            //		[*/if*]
		
            'multi'          : true,
            //		[*if $file_type*]
		
            'fileDesc'	 	 : '[*$type_desc*]([*$file_type.types*])',
            'fileExt'	 	 : '[*$file_type.types*]',
            'sizeLimit'		 : '[*$file_type.max_filesize*]',
            //		[*/if*]

            'buttonText'	 : '[*'browse'|lang|sl*]',
            'onComplete'	 : function (event, queueID, fileObj, response) {
        fileObj.name = html_encode(fileObj.name);
    [*if !$onComplete*]
                        if (is_ok(response))
                            alert("[*'success'|lang|sl*]!");
                        else
                            alert("[*'error'|lang|sl*]: "+response);
    [*else*]
        [*$onComplete*](response, '[*$postfix|sl*]', fileObj);
    [*/if*]
                }
            });
        });
</script>
[*if $print_divs*]
    <div align="left" id="fileQueue_[*$postfix*]"></div>
    <input type="file" name="uploadify" id="uploadify_[*$postfix*]">
    [*if !$auto*]
        <br>
        <a href="javascript:void(0);"
           onclick="jQuery('#uploadify_[*$postfix|sl*]').uploadifyUpload();">[*'upload_files'|lang*]</a>
    [*/if*]
[*/if*]
