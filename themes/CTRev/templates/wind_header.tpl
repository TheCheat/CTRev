<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <base href="[*$baseurl*]">
        <title>[*'site_title'|config*]</title>
        <link rel="stylesheet" href="[*$theme_path*]css/style.css" type="text/css">
        [*if $color_path*]
            <link rel="stylesheet" href="[*$theme_path*]css/[*$color_path*]color.css"
                  type="text/css">
        [*/if*]
        [*include file="initializer.tpl"*]
    </head>
    <body>