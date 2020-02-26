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

    /**
     * @param string $field
     * @param string $type
     * @param string $label
     * @param string $help
     * @return string
     */
    public function input($field, $type, $label = '', $help = '')
    {
        $input = '';
        $input .= '<div class="form-group field-' . $this->_tableName . $field . '">';
        $input .= '<label class="control-label" for="' . $this->_tableName . '-' . $field . '">' . (($label === '') ? ($this->attributeLabels()[$field] ?? $field) : $label) . '</label>';
        $input .= '<input type="' . $type . '" id="' . $this->_tableName . '-' . $field . '" class="form-control" name="' . $this->_tableName  . '[' . $field . ']" value="' . $this->$field . '" >';
        $input .= '<div id="' . $this->_tableName  . '[' . $field . ']-help" class="form-text text-muted">' . $help . '</div>';

        return $input;
    }

    /**
     * @param string $field
     * @param string $label
     * @param string $help
     * @return string
     */
    public function textInput($field, $label = '', $help = '')
    {
        return $this->input($field, 'text', $label = '', $help = '');
    }
}