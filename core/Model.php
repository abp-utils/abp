<?php

namespace abp\core;

use abp\component\Form;
use abp\component\StringHelper;
use Abp;

/**
 * Class Model
 * @package abp\core
 */
class Model extends Form
{
    const MODEL_DEFAULT_FOLDER = '\model\\';

    public const DEFAULT_CREATE_TIME_FIELD = 'created_time';
    public const DEFAULT_UPDATE_TIME_FIELD = 'updated_time';

    protected $_attributes = [];
    protected $_oldAttributes = [];
    protected $_changeAttributes = [];
    protected $_unsetAttributes = [];
    protected $_relations = [];
    protected $_relationsClass = [];

    public $_tableName = null;

    /**
     * Model constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class, $params = [])
    {
        $this->_tableName = StringHelper::conversionFilename($class);
        if ($class::tableName() !== null) {
            $this->_tableName = $class::tableName();
        } else {
            $this->_tableName = StringHelper::conversionFilename($class);
        }
        $paramsSave = $params;
        foreach ($params as $field => $value) {
            $fieldInfo = explode('.', $field);
            if (count($fieldInfo) == 1) {
                continue;
            }
            if ($fieldInfo[0] == $this->_tableName) {
                unset($paramsSave[$field]);
                $paramsSave[$fieldInfo[1]] = $value;
            } else {
                $className = self::MODEL_DEFAULT_FOLDER . StringHelper::revertConversionFilename($fieldInfo[0]);
                $this->_relations[$className][$fieldInfo[1]] = $value;
                unset($paramsSave[$field]);
            }
        }

        $this->_attributes = $paramsSave;
        $this->_oldAttributes = $paramsSave;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $modelClassname = self::MODEL_DEFAULT_FOLDER . $name;
        if (isset($this->_relations[$modelClassname]) && class_exists($modelClassname)) {
            if (!isset($this->_relationsClass[$modelClassname])) {
                $this->_relationsClass[$modelClassname] = new $modelClassname($modelClassname, $this->_relations[$modelClassname]);
            }
            return $this->_relationsClass[$modelClassname];
        }

        if (!in_array($name, array_keys($this->_attributes))) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        return $this->_attributes[$name];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        if (!in_array($name, array_keys($this->_attributes))) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        if ($this->_attributes[$name] != $value) {
            $this->_attributes[$name] = $value;
            $this->_changeAttributes[$name] = $value;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return in_array($name, array_keys($this->_attributes));
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        if (!in_array($name, array_keys($this->_attributes))) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        $this->_unsetAttributes[$name] = true;
        unset($this->_attributes[$name]);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            '_attributes:protected' => $this->_attributes,
            '_oldAttributes:protected' => $this->_oldAttributes,
            '_relations:protected' => $this->_relations,
            '_changeAttributes:protected' => $this->_changeAttributes,
            '_unsetAttributes:protected' => $this->_unsetAttributes,
        ];
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }

    /**
     * @return array
     */
    public function getChangeAttributes()
    {
        return $this->_changeAttributes;
    }

    /**
     * @return array
     */
    public function getUnsetAttributes()
    {
        return $this->_unsetAttributes;
    }

    public function beforeLoad(array $data): bool
    {
        return true;
    }

    public function afterLoad(array $data): bool
    {
        return true;
    }

    public function emptyLoad(array $data): bool
    {
        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function load($data)
    {
        if (!isset($data[$this->_tableName])) {
            return false;
        }
        $dataModel = $data[$this->_tableName];
        $this->beforeLoad($dataModel);

        foreach ($dataModel as $field => $value) {
            if (!((isset($this->$field) || property_exists(get_class($this), $field)))) {
                continue;
            }

            $this->$field = $value;
        }
        if (!empty($this->getChangeAttributes())) {
            $this->afterLoad($dataModel);
            return true;
        }
        $this->emptyLoad($dataModel);
        return false;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * @return array
     */
    public function changingAttributes()
    {
        return [];
    }

    /**
     * Exsample:
     * Class::class => ['one', 'column_id'],
     * Class::class => ['one', 'column_id', 'column_id_table_relation'],
     */
    public static function relation(): array
    {
        return [];
    }

    /**
     * @return string|null
     */
    public static function tableName()
    {
        return null;
    }
}



