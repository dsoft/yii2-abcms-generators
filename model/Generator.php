<?php

namespace abcms\generators\model;

use yii\db\Schema;
use yii\base\NotSupportedException;

class Generator extends \yii\gii\generators\model\Generator
{

    /**
     * @var array The attributes that should be ignored in the rules generation
     */
    public $ignoreAttributes = [
        'time', 'deleted', 'ipAddress'
    ];

    /**
     * @var array Possible names for images attributes
     */
    public $imagesAttributes = [
        'image', 'thumb', 'thumbnail', 'logo', 'background', 'image1', 'image2'
    ];

    /**
     *
     * @var array Posiible name of attributes that should be translated
     * Where key is the attribute name, and value is how it should be displayed in the Model behavior
     */
    public $translationAttributes = [
        'title' => 'title',
        'description' => 'description:text-editor',
        'name' => 'name',
        'smallDescription' => 'smallDescription',
        'question' => 'question',
        'answer' => 'answer:text-editor'
    ];

    /**
     * @var string time attribute name
     */
    public $timeAttribute = 'time';
    
    /**
     * @var string ip address attribute name
     */
    public $ipAddressAttribute = 'ipAddress';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ABCMS Model Generator';
    }
    
    /**
     * {@inheritdoc}
     */
    public function formView()
    {
        $class = new \ReflectionClass('\yii\gii\generators\model\Generator');
        return dirname($class->getFileName()) . '/form.php';
    }

    /**
     * Returns images attributes for certain table.
     * Compare table attributes to [[imagesAttributes]] attribute
     * @param \yii\db\TableSchema $table
     * @return array
     */
    public function imagesAttributes($table)
    {
        $array = [];
        foreach($table->columnNames as $name) {
            if(in_array($name, $this->imagesAttributes)) {
                $array[] = $name;
            }
        }
        return $array;
    }

    /**
     * Returns translation attributes for certain table.
     * Compare table attributes to [[translationAttributes]] keys
     * @param \yii\db\TableSchema $table
     * @return array
     */
    public function translationAttributes($table)
    {
        $array = [];
        $attributes = $this->translationAttributes;
        foreach($table->columnNames as $name) {
            if(key_exists($name, $attributes)) {
                $array[$name] = $attributes[$name];
            }
        }
        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if(in_array($column->name, $this->ignoreAttributes) || in_array($column->name, $this->imagesAttributes)) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];
        $driverName = $this->getDbDriverName();
        foreach ($types as $type => $columns) {
            if ($driverName === 'pgsql' && $type === 'integer') {
                $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => null]";
            }
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        $db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }

    /**
     * Return if the model has a certain field
     * @param string $fieldName
     * @param \yii\db\TableSchema $table
     * @return boolean
     */
    public function hasField($fieldName, $table)
    {
        $result = FALSE;
        foreach($table->columnNames as $name) {
            if($name == $fieldName) {
                return TRUE;
            }
        }
        return $result;
    }

}
