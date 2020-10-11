<?php

namespace abp\database;

use abp\component\Api;
use abp\core\Model;
use abp\database\Query;

class ActiveRecord extends Query
{
    public $_isNewRecord = false;

    public function __construct($class = null, $params = [])
    {
        if ($class !== null) {
            parent::__construct($class, $params);
            return;
        }

        $this->_isNewRecord = true;
        $class = get_called_class();
        parent::__construct($class, $params);
        $describe = $this->describe();
        $schema = [];
        foreach ($describe as $value) {
            $schema[$value['Field']] = false;
        }
        parent::__construct($class, $schema);
    }

    /**
     * @return bool
     */
    public function beforeSave($isNewRecord)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterSave() {}

    public function save()
    {
        $result = $this->beforeSave($this->_isNewRecord);
        if (!$result) {
            return false;
        }

        $attributes = [];
        $values = [];
        $timestampArray = [];
        if ($this->_isNewRecord && $timestampArray = $this->createTimestamp()) {
            $timestampIndex = array_key_first($timestampArray);
        } else if ($timestampArray = $this->updateTimestamp()){
            $timestampIndex = array_key_first($timestampArray);
        }
        if (isset($timestampArray[$timestampIndex])) {
            $this->$timestampIndex = $timestampArray[$timestampIndex];
        }
        foreach ($this->_changeAttributes as $key => $value) {
            $attributes[] = $key;
            $valueAttributes = isset($this->changingAttributes()[$key]) ? $this->changingAttributes()[$key]() : $value;
            $valueAttributes = $valueAttributes === false ? null : $valueAttributes;
            $values[] = $valueAttributes;
        }

        if (!$this->_isNewRecord) {
            $identityRecord = array_slice($this->_attributes, 0 , 1);
            $result = $this->update($attributes, $values)->where($identityRecord)->commandExec();
            $this->afterSave();
            return $result;
        }
        $pk = $this->getPrimaryKey();
        $insertId = $this->insert($attributes, $values);
        $this->$pk = $insertId;

        return $insertId;
    }

    /**
     * @return array|bool
     */
    public function createTimestamp()
    {
        return [Model::DEFAULT_CREATE_TIME_FIELD => time()];
    }

    /**
     * @return array|bool
     */
    public function updateTimestamp()
    {
        return [Model::DEFAULT_UPDATE_TIME_FIELD => time()];
    }
}

