<?php

$languages = array(
    "patterns_title" => "Шаблоны раздач",
    'patterns_none' => "На данный момент не создано шаблонов раздач",
    'patterns_pattern_id' => "ID шаблона",
    'patterns_pattern_name' => "Имя шаблона",
    'patterns_pattern_add' => "Добавление шаблона",
    "patterns_pattern_edit" => "Редактирование шаблона",
    "patterns_add_by_this" => "Добавить шаблон, основываясь на данном",
    "patterns_area_pattern_name" => "Имя шаблона",
    "patterns_area_name" => "Имя поля",
    "patterns_area_rname" => "Имя элемента",
    "patterns_area_type" => "Тип(и <abbr title='если доступен'>размер</abbr>)",
    "patterns_types_input" => "Однострочное поле",
    "patterns_types_small_input" => "Малое однострочное поле",
    "patterns_types_textarea" => "Текстовое поле",
    "patterns_types_radio" => "Поле-выборка",
    "patterns_types_select" => "Список",
    "patterns_types_html" => "HTML код",
    "patterns_area_descr" => "Описание поля",
    "patterns_area_values" => "Значения для выборки",
    "patterns_area_html" => "HTML код",
    "patterns_area_formdata" => "Запись в форму",
    "patterns_areas_support" => "*Подсказка: 
        Если перед <b>Именем поля</b> поставить знак '<b>*</b>' поле становится обязательным
        к заполнению.<br>
        '<b>Имя элемента</b>' должно состоять <b>только из латинских букв</b>!<br>
        Значения в поле '<b>Значения для выборки</b>' писать через знак '<b>;</b>',
        если отличается вставляемое значение и значение отображённое, то пишем их
        через знак '<b>:</b>'.<br>
        Заполнение поля '<b>Запись в форму</b>' идёт по следующим правилам:<br>
        <b>{form.что-нибудь}</b> - поле основной формы, 
        после него пишется значение, каждое поле с новой строки<br>
    <b>{this.name}</b> - значение поля формы шаблона с <b>именем элемента</b> 'name'<br>
    <b>{this.\$value}</b> - значение поля формы шаблона <b>не HTML типа</b>.<br>
    <b>{nobr}</b> - не добавлять знак переноса на новую строку(ставится после указания формы).<br>
    <b>{br}</b> - добавить знак переноса на новую строку.<br>
    Например: {form.title}{nobr}{this.\$value}<br>
        {form.content}[b]Название:[/b] {this.name}",
    'patterns_invalid_data' => "Неверные входные данные",
    'patterns_are_you_sure_want_to_delete' => "Вы уверены, что хотите удалить этот шаблон?",
    'patterns_necessary_fields' => "*Примечание: поля отмеченные звёздочкой '*' обязательны к заполнению.",
    'patterns_filling_by_pattern' => 'Заполнение по шаблону',
    'patterns_necessary_fields_not_filled' => 'Обязательные поля(отмечены звёздочкой "*"), не заполнены'
);
?>