<?php

namespace abp\component;

use Abp;

class Html
{
    const TABLE_DATE_FORMAT  = [
        'date' => 'd.m.Y',
        'time' => 'h:i',
        'datetime' => 'd.m.Y H:i',
        'datetimeFull' => 'd.m.Y H:i:s',
    ];

    const TEMPLATE = [
        'view' => 'fa-eye',
        'update' => 'fa-pencil',
        'delete' => 'fa-trash',
    ];

    const TEMPLATE_TITLE = [
        'view' => 'Просмотр',
        'update' => 'Редактировать',
        'delete' => 'Удалить',
    ];

    public static function table($attributes)
    {
        $htmlTable = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr>';

        $columns = $attributes['columns'];
        $models = $attributes['models'];
        $model = null;
        if (isset($attributes['models'][0])) {
            $model = $attributes['models'][0];
        }

        if ($model === null) {
            return 'Таблица пуста';
        }
        $labels = $model->attributeLabels();
        $columnsText = [];
        $columnsTemp = [];
        foreach ($columns as $key => $column) {
            $columnsTemp[] = $column;
            $columnsText[$key] = isset($labels[$column['attribute']]) ? $labels[$column['attribute']] : $column['attribute'];
            $htmlTable .= '<th scope="col">' . $columnsText[$key]. '</th>';
        }
        $htmlTable .= '</tr></thead><tbody>';

        $columns = $columnsTemp;

        foreach ($models as $model) {
            foreach ($columns as $column) {
                if (isset($column['template'])) {
                    if (!isset(self::TEMPLATE[$column['template']])) {
                        continue;
                    }
                    $indexColumn = $model->_tableName . '_id';
                    $controller = $model->_tableName;
                    $htmlTable .= '<td><a class="a-black" href="/' . $controller . '/' . $column['template'] . '?id=' . $model->$indexColumn . '" title="' .self::TEMPLATE_TITLE[$column['template']]  . '" aria-label="' .self::TEMPLATE_TITLE[$column['template']]  . '"><i class="fa ' . self::TEMPLATE[$column['template']] . ' fa-fw"></i></a></td>';
                    continue;
                }
                $modelColumn = $column['attribute'];
                if (isset($model->$modelColumn)) {
                    $value = $model->$modelColumn;
                    if (isset($column['date-format'])) {
                        if (isset(self::TABLE_DATE_FORMAT[$column['date-format']])) {
                            $value = date(self::TABLE_DATE_FORMAT[$column['date-format']], $model->$modelColumn);
                        }
                    }
                    if (isset($column['value'])) {
                        $value = call_user_func($column['value'], $model);
                    }
                    if ($value === null) {
                        $value = '(Пустое значение)';
                        $htmlTable .=  '<td class="table-empty-value">' . $value .'</td>';
                    } else {
                        $htmlTable .=  '<td>' . $value .'</td>';
                    }
                }
            }
            $htmlTable .= '</tr>';
        }
        $htmlTable .= '</tbody></table></div>';

        return $htmlTable;
    }
}


