<?php

namespace abp\component;

use Abp;

class Html
{
    const TABLE_TEMPLATE  = [
        'date' => 'd.m.Y',
        'time' => 'h:i',
        'datetime' => 'd.m.Y H:i',
        'datetimeFull' => 'd.m.Y H:i:s',
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
                $modelColumn = $column['attribute'];
                if (isset($model->$modelColumn)) {
                    $value = $model->$modelColumn;
                    if (isset($column['template'])) {
                        if (isset(self::TABLE_TEMPLATE[$column['template']])) {
                            $value = date(self::TABLE_TEMPLATE[$column['template']], $model->$modelColumn);
                        }
                    }
                    if (isset($column['value'])) {
                        $value = call_user_func($column['value'], $model);
                    }
                    if (empty($value)) {
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
