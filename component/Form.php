<?php

namespace abp\component;

use Abp;

class Form
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    /**
     * @param string|null $action
     * @param string $method
     * @return string
     */
    public function beginForm($action = null, $method = self::METHOD_POST)
    {
        if ($action === null) {
            $action = Abp::$requestString . '?' . Abp::$requestGet;
        }

        return '<form id="' . $this->_tableName . 'Form" action="' .$action .'" method="' . $method .'">';
    }

    /**
     * @param bool $submit
     * @param string $submitText
     * @return string|void
     */
    public function endForm($submit = true, $submitText = 'Сохранить')
    {
        if (!$submit) {
            return;
        }
        $button = '<button type="submit" class="btn btn-primary">' . $submitText . '</button>';
        return $button . '</form>';
    }

    private function getStandartBeginField($field, $label)
    {
        $fieldHtml = '';
        $fieldHtml .= '<div class="form-group field-' . $this->_tableName  . '[' . $field . ']' . '">';
        $fieldHtml .= '<label class="control-label" for="' . $this->_tableName . '-' . $field . '">' . (($label === '') ? ($this->attributeLabels()[$field] ?? $field) : $label) . '</label>';

        return $fieldHtml;
    }

    private function getStandartEndField()
    {
        return '</div>';
    }

    /**
     * @param string $field
     * @param string $type
     * @param string $label
     * @param string $help
     * @param string $attr
     * @return string
     */
    public function input($field, $type, $label = '', $help = '', $value = null, $attr = '')
    {
        $input = '';
        $input .= $this->getStandartBeginField($field, $label);
        $input .= '<input type="' . $type . '" id="' . $this->_tableName . '-' . $field . '" class="form-control" name="' . $this->_tableName  . '[' . $field . ']" value="' . ($value ?? $this->$field) . '" ' . $attr .'>';
        $input .= '<div id="' . $this->_tableName  . '[' . $field . ']-help" class="form-text text-muted">' . $help . '</div>';
        $input .= $this->getStandartEndField();

        return $input;
    }

    /**
     * @param string $field
     * @param string $label
     * @param string $help
     * @return string
     */
    public function textInput($field, $label = '', $help = '', $value = null)
    {
        return $this->input($field, 'text', $label = '', $help = '', $value);
    }

    /**
     * @param string $field
     * @param string $label
     * @param string $help
     * @return string
     */
    public function textInputDisable($field, $label = '', $help = '', $value = null)
    {
        return $this->input($field, 'text', $label, $help, $value, 'readonly');
    }

    /**
     * @param string $field
     * @param string $label
     * @param string $help
     * @return string
     */
    public function inputHidden($field, $label = '', $help = '')
    {
        $input = '';
        $input .= '<input type="hidden" name="' . $this->_tableName  . '[' . $field . ']" value="' . $this->$field . '">';
        return $input;
    }

    public function dropDownList($field, $list, $label = '', $attr = '')
    {
        $select = '';
        $select .= $this->getStandartBeginField($field, $label);
        $select .= '<select id="' . $this->_tableName . '-' . $field . '" class="form-control" name="' . $this->_tableName  . '[' . $field . ']" '. $attr .'>';
        if (!is_array($list)) {
            $list = [$list];
        }

        foreach ($list as $value => $item) {
            $selected = $value == $this->$field ? 'selected' : '';
            $select .= "<option value='$value' $selected>$item</option>";
        }
        $select .= '</select>';
        $select .= $this->getStandartEndField();

        return $select;
    }

    public function dropDownListDisable($field, $list, $label = '')
    {
        return $this->dropDownList($field, $list, $label, 'disabled');
    }

    public function multiDropDownList()
    {
        throw new \InvalidArgumentException('Метод не реализован.');
    }

    public function checkBox($field, $list, $value = '', $label = '')
    {
        $checkbox = '';
        $checkbox .= $this->getStandartBeginField($field, $label);
        if (!is_array($list)) {
            $list = [$list];
        }

        $checkbox .= '<div id="' . $this->_tableName . '-' . $field . '">';
        foreach ($list as $value => $item) {
            $checked = $value == $this->$field ? 'checked' : '';
            $checkbox .= '<div class="form-check form-check-inline">';
            $checkbox .= '<label>';
            $checkbox .= '<input class="form-check-input" type="radio" name="' . $this->_tableName  . '[' . $field . ']" value="' . $value . '" ' . $checked .'>';
            $checkbox .= $item;
            $checkbox .= '</label>';
            $checkbox .= '</div>';
        }
        $checkbox .= '</div>';

        $checkbox .= $this->getStandartEndField();

        return $checkbox;
    }
}