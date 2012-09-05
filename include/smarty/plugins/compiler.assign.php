<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Parsing expressions in Smarty(now avaliable only parsing of arrays)
 * @param type $expr
 * @return type 
 */
function smarty_parse_expr($expr) {
    $expr = trim($expr, ' \'"');
    switch ($expr[0]) {
        case "a":
            $arr = array();
            $expr = mb_substr($expr, 2, mb_strlen($expr) - 3);
            $expr = preg_split('/(?!\\\)\,/siu', $expr);
            $c = count($expr);
            for ($i = 0; $i < $c; $i++) {
                $t = $expr[$i];
                if (preg_match('/^(.+)(?!\\\)\=(.+?)$/siu', $t, $m))
                    $arr[$m[1]] = $m[2];
                else
                    $arr[] = $t;
            }
            return var_export($arr, true);
            break;
    }
    return null;
}

/**
 * Smarty {assign} compiler function plugin
 *
 * Type:     compiler function<br>
 * Name:     assign<br>
 * Purpose:  assign a value to a template variable
 * @link http://smarty.php.net/manual/en/language.custom.functions.php#LANGUAGE.FUNCTION.ASSIGN {assign}
 *       (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com> (initial author)
 * @author messju mohr <messju at lammfellpuschen dot de> (conversion to compiler function)
 * @param string containing var-attribute and value-attribute
 * @param Smarty_Compiler
 */
function smarty_compiler_assign($tag_attrs, &$compiler) {
    $_params = $compiler->_parse_attrs($tag_attrs);

    if (!isset($_params ['var'])) {
        $compiler->_syntax_error("assign: missing 'var' parameter", E_USER_WARNING);
        return;
    }

    if (isset($_params ['expr']) && !isset($_params ['value']))
        $_params ['value'] = smarty_parse_expr($_params ['expr']);

    if (!isset($_params ['value'])) {
        $compiler->_syntax_error("assign: missing 'value' or 'expr' parameter", E_USER_WARNING);
        return;
    }


    return "\$this->assign({$_params['var']}, {$_params['value']});";
}

/* vim: set expandtab: */
?>
