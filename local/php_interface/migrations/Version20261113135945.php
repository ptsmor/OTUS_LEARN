<?php

namespace Sprint\Migration;


class Version20261113135945 extends Version
{
    protected $author = "admin7";

    protected $description = "тест миграции";

    protected $moduleVersion = "5.16.0";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Iblock()->saveIblockType(array (
  'ID' => 'structure',
  'SECTIONS' => 'Y',
  'EDIT_FILE_BEFORE' => NULL,
  'EDIT_FILE_AFTER' => NULL,
  'IN_RSS' => 'N',
  'SORT' => '20',
  'LANG' => 
  array (
    'ru' => 
    array (
      'NAME' => 'Оргструктура',
      'SECTION_NAME' => 'Разделы',
      'ELEMENT_NAME' => 'Элементы',
    ),
    'en' => 
    array (
      'NAME' => 'Company structure',
      'SECTION_NAME' => 'Sections',
      'ELEMENT_NAME' => 'Elements',
    ),
  ),
));
        $iblockId = $helper->Iblock()->saveIblock(array (
  'IBLOCK_TYPE_ID' => 'structure',
  'LID' => 
  array (
    0 => 's1',
  ),
  'CODE' => 'absence',
  'API_CODE' => NULL,
  'REST_ON' => 'N',
  'NAME' => 'График отсутствий',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'LIST_PAGE_URL' => NULL,
  'DETAIL_PAGE_URL' => NULL,
  'SECTION_PAGE_URL' => NULL,
  'CANONICAL_PAGE_URL' => NULL,
  'PICTURE' => NULL,
  'DESCRIPTION' => NULL,
  'DESCRIPTION_TYPE' => 'text',
  'RSS_TTL' => '24',
  'RSS_ACTIVE' => 'Y',
  'RSS_FILE_ACTIVE' => 'N',
  'RSS_FILE_LIMIT' => NULL,
  'RSS_FILE_DAYS' => NULL,
  'RSS_YANDEX_ACTIVE' => 'N',
  'XML_ID' => 'absence',
  'INDEX_ELEMENT' => 'N',
  'INDEX_SECTION' => 'N',
  'WORKFLOW' => 'N',
  'BIZPROC' => 'N',
  'SECTION_CHOOSER' => 'L',
  'LIST_MODE' => NULL,
  'RIGHTS_MODE' => 'S',
  'SECTION_PROPERTY' => NULL,
  'PROPERTY_INDEX' => NULL,
  'VERSION' => '1',
  'LAST_CONV_ELEMENT' => '0',
  'SOCNET_GROUP_ID' => NULL,
  'EDIT_FILE_BEFORE' => NULL,
  'EDIT_FILE_AFTER' => NULL,
  'SECTIONS_NAME' => 'Разделы',
  'SECTION_NAME' => 'Раздел',
  'ELEMENTS_NAME' => 'Отсутствия',
  'ELEMENT_NAME' => 'Отсутствие',
  'FULLTEXT_INDEX' => 'N',
  'EXTERNAL_ID' => 'absence',
  'LANG_DIR' => '/',
  'IPROPERTY_TEMPLATES' => 
  array (
  ),
  'ELEMENT_ADD' => 'Добавить отсутствие',
  'ELEMENT_EDIT' => 'Изменить отсутствие',
  'ELEMENT_DELETE' => 'Удалить отсутствие',
  'SECTION_ADD' => 'Добавить раздел',
  'SECTION_EDIT' => 'Изменить раздел',
  'SECTION_DELETE' => 'Удалить раздел',
));
        $helper->Iblock()->saveIblockFields($iblockId, array (
  'IBLOCK_SECTION' => 
  array (
    'NAME' => 'Привязка к разделам',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'KEEP_IBLOCK_SECTION_ID' => 'N',
    ),
    'VISIBLE' => 'Y',
  ),
  'ACTIVE' => 
  array (
    'NAME' => 'Активность',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => 'Y',
    'VISIBLE' => 'Y',
  ),
  'ACTIVE_FROM' => 
  array (
    'NAME' => 'Начало активности',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'ACTIVE_TO' => 
  array (
    'NAME' => 'Окончание активности',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'SORT' => 
  array (
    'NAME' => 'Сортировка',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => '0',
    'VISIBLE' => 'Y',
  ),
  'NAME' => 
  array (
    'NAME' => 'Название',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'PREVIEW_PICTURE' => 
  array (
    'NAME' => 'Картинка для анонса',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'FROM_DETAIL' => 'N',
      'UPDATE_WITH_DETAIL' => 'N',
      'DELETE_WITH_DETAIL' => 'N',
      'SCALE' => 'N',
      'WIDTH' => '',
      'HEIGHT' => '',
      'IGNORE_ERRORS' => 'N',
      'METHOD' => 'resample',
      'COMPRESSION' => 95,
      'USE_WATERMARK_TEXT' => 'N',
      'WATERMARK_TEXT' => '',
      'WATERMARK_TEXT_FONT' => '',
      'WATERMARK_TEXT_COLOR' => '',
      'WATERMARK_TEXT_SIZE' => '',
      'WATERMARK_TEXT_POSITION' => '',
      'USE_WATERMARK_FILE' => 'N',
      'WATERMARK_FILE' => '',
      'WATERMARK_FILE_ALPHA' => '',
      'WATERMARK_FILE_POSITION' => '',
      'WATERMARK_FILE_ORDER' => '',
    ),
    'VISIBLE' => 'Y',
  ),
  'PREVIEW_TEXT_TYPE' => 
  array (
    'NAME' => 'Тип описания для анонса',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => 'text',
    'VISIBLE' => 'Y',
  ),
  'PREVIEW_TEXT' => 
  array (
    'NAME' => 'Описание для анонса',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'DETAIL_PICTURE' => 
  array (
    'NAME' => 'Детальная картинка',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'SCALE' => 'N',
      'WIDTH' => '',
      'HEIGHT' => '',
      'IGNORE_ERRORS' => 'N',
      'METHOD' => 'resample',
      'COMPRESSION' => 95,
      'USE_WATERMARK_TEXT' => 'N',
      'WATERMARK_TEXT' => '',
      'WATERMARK_TEXT_FONT' => '',
      'WATERMARK_TEXT_COLOR' => '',
      'WATERMARK_TEXT_SIZE' => '',
      'WATERMARK_TEXT_POSITION' => '',
      'USE_WATERMARK_FILE' => 'N',
      'WATERMARK_FILE' => '',
      'WATERMARK_FILE_ALPHA' => '',
      'WATERMARK_FILE_POSITION' => '',
      'WATERMARK_FILE_ORDER' => '',
    ),
    'VISIBLE' => 'Y',
  ),
  'DETAIL_TEXT_TYPE' => 
  array (
    'NAME' => 'Тип детального описания',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => 'text',
    'VISIBLE' => 'Y',
  ),
  'DETAIL_TEXT' => 
  array (
    'NAME' => 'Детальное описание',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'XML_ID' => 
  array (
    'NAME' => 'Внешний код',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'CODE' => 
  array (
    'NAME' => 'Символьный код',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'UNIQUE' => 'N',
      'TRANSLITERATION' => 'N',
      'TRANS_LEN' => 100,
      'TRANS_CASE' => 'L',
      'TRANS_SPACE' => '-',
      'TRANS_OTHER' => '-',
      'TRANS_EAT' => 'Y',
      'USE_GOOGLE' => 'N',
    ),
    'VISIBLE' => 'Y',
  ),
  'TAGS' => 
  array (
    'NAME' => 'Теги',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'SECTION_NAME' => 
  array (
    'NAME' => 'Название',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'SECTION_PICTURE' => 
  array (
    'NAME' => 'Картинка для анонса',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'FROM_DETAIL' => 'N',
      'UPDATE_WITH_DETAIL' => 'N',
      'DELETE_WITH_DETAIL' => 'N',
      'SCALE' => 'N',
      'WIDTH' => '',
      'HEIGHT' => '',
      'IGNORE_ERRORS' => 'N',
      'METHOD' => 'resample',
      'COMPRESSION' => 95,
      'USE_WATERMARK_TEXT' => 'N',
      'WATERMARK_TEXT' => '',
      'WATERMARK_TEXT_FONT' => '',
      'WATERMARK_TEXT_COLOR' => '',
      'WATERMARK_TEXT_SIZE' => '',
      'WATERMARK_TEXT_POSITION' => '',
      'USE_WATERMARK_FILE' => 'N',
      'WATERMARK_FILE' => '',
      'WATERMARK_FILE_ALPHA' => '',
      'WATERMARK_FILE_POSITION' => '',
      'WATERMARK_FILE_ORDER' => '',
    ),
    'VISIBLE' => 'Y',
  ),
  'SECTION_DESCRIPTION_TYPE' => 
  array (
    'NAME' => 'Тип описания',
    'IS_REQUIRED' => 'Y',
    'DEFAULT_VALUE' => 'text',
    'VISIBLE' => 'Y',
  ),
  'SECTION_DESCRIPTION' => 
  array (
    'NAME' => 'Описание',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'SECTION_DETAIL_PICTURE' => 
  array (
    'NAME' => 'Детальная картинка',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'SCALE' => 'N',
      'WIDTH' => '',
      'HEIGHT' => '',
      'IGNORE_ERRORS' => 'N',
      'METHOD' => 'resample',
      'COMPRESSION' => 95,
      'USE_WATERMARK_TEXT' => 'N',
      'WATERMARK_TEXT' => '',
      'WATERMARK_TEXT_FONT' => '',
      'WATERMARK_TEXT_COLOR' => '',
      'WATERMARK_TEXT_SIZE' => '',
      'WATERMARK_TEXT_POSITION' => '',
      'USE_WATERMARK_FILE' => 'N',
      'WATERMARK_FILE' => '',
      'WATERMARK_FILE_ALPHA' => '',
      'WATERMARK_FILE_POSITION' => '',
      'WATERMARK_FILE_ORDER' => '',
    ),
    'VISIBLE' => 'Y',
  ),
  'SECTION_XML_ID' => 
  array (
    'NAME' => 'Внешний код',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => '',
    'VISIBLE' => 'Y',
  ),
  'SECTION_CODE' => 
  array (
    'NAME' => 'Символьный код',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => 
    array (
      'UNIQUE' => 'N',
      'TRANSLITERATION' => 'N',
      'TRANS_LEN' => 100,
      'TRANS_CASE' => 'L',
      'TRANS_SPACE' => '-',
      'TRANS_OTHER' => '-',
      'TRANS_EAT' => 'Y',
      'USE_GOOGLE' => 'N',
    ),
    'VISIBLE' => 'Y',
  ),
  'LOG_SECTION_ADD' => 
  array (
    'NAME' => 'LOG_SECTION_ADD',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => NULL,
    'VISIBLE' => 'Y',
  ),
  'LOG_SECTION_EDIT' => 
  array (
    'NAME' => 'LOG_SECTION_EDIT',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => NULL,
    'VISIBLE' => 'Y',
  ),
  'LOG_SECTION_DELETE' => 
  array (
    'NAME' => 'LOG_SECTION_DELETE',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => NULL,
    'VISIBLE' => 'Y',
  ),
  'LOG_ELEMENT_ADD' => 
  array (
    'NAME' => 'LOG_ELEMENT_ADD',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => NULL,
    'VISIBLE' => 'Y',
  ),
  'LOG_ELEMENT_EDIT' => 
  array (
    'NAME' => 'LOG_ELEMENT_EDIT',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => NULL,
    'VISIBLE' => 'Y',
  ),
  'LOG_ELEMENT_DELETE' => 
  array (
    'NAME' => 'LOG_ELEMENT_DELETE',
    'IS_REQUIRED' => 'N',
    'DEFAULT_VALUE' => NULL,
    'VISIBLE' => 'Y',
  ),
));
    $helper->Iblock()->saveGroupPermissions($iblockId, array (
  'administrators' => 'X',
  'everyone' => 'R',
  'PERSONNEL_DEPARTMENT' => 'W',
  'PORTAL_ADMINISTRATION_s1' => 'X',
));
        $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Пользователь',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'USER',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => '1',
  'FILE_TYPE' => NULL,
  'MULTIPLE_CNT' => '0',
  'LINK_IBLOCK_ID' => NULL,
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'Y',
  'VERSION' => '1',
  'USER_TYPE' => 'employee',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => NULL,
  'SECTION_PROPERTY' => 'N',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Состояние завершения',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'FINISH_STATE',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => '2',
  'FILE_TYPE' => NULL,
  'MULTIPLE_CNT' => '0',
  'LINK_IBLOCK_ID' => NULL,
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '1',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => NULL,
  'SECTION_PROPERTY' => 'N',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Состояние',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'STATE',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => '11',
  'FILE_TYPE' => NULL,
  'MULTIPLE_CNT' => '0',
  'LINK_IBLOCK_ID' => NULL,
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '1',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => NULL,
  'SECTION_PROPERTY' => 'N',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Тип отсутствия',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ABSENCE_TYPE',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'L',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => '67',
  'FILE_TYPE' => NULL,
  'MULTIPLE_CNT' => '0',
  'LINK_IBLOCK_ID' => NULL,
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '1',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => NULL,
  'VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'отпуск ежегодный',
      'DEF' => 'Y',
      'SORT' => '100',
      'XML_ID' => 'VACATION',
    ),
    1 => 
    array (
      'VALUE' => 'командировка',
      'DEF' => 'N',
      'SORT' => '200',
      'XML_ID' => 'ASSIGNMENT',
    ),
    2 => 
    array (
      'VALUE' => 'больничный',
      'DEF' => 'N',
      'SORT' => '300',
      'XML_ID' => 'LEAVESICK',
    ),
    3 => 
    array (
      'VALUE' => 'отпуск декретный',
      'DEF' => 'N',
      'SORT' => '400',
      'XML_ID' => 'LEAVEMATERINITY',
    ),
    4 => 
    array (
      'VALUE' => 'отгул за свой счет',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'LEAVEUNPAYED',
    ),
    5 => 
    array (
      'VALUE' => 'прогул',
      'DEF' => 'N',
      'SORT' => '600',
      'XML_ID' => 'UNKNOWN',
    ),
    6 => 
    array (
      'VALUE' => 'другое',
      'DEF' => 'N',
      'SORT' => '700',
      'XML_ID' => 'OTHER',
    ),
    7 => 
    array (
      'VALUE' => 'персональные календари',
      'DEF' => 'N',
      'SORT' => '800',
      'XML_ID' => 'PERSONAL',
    ),
  ),
  'SECTION_PROPERTY' => 'N',
));
            $helper->UserOptions()->saveElementForm($iblockId, array (
  'Отсутствие|edit1' => 
  array (
    'PROPERTY_USER' => 'Выберите отсутствующего сотрудника',
    'PROPERTY_ABSENCE_TYPE' => 'Тип отсутствия',
    'NAME' => 'Причина отсутствия',
    'edit1_csection1' => 'Период отсутствия',
    'ACTIVE_FROM' => 'Начало',
    'ACTIVE_TO' => 'Окончание',
  ),
));

    }
}
