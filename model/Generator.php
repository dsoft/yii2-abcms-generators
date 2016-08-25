<?php

namespace abcms\library\generators\model;

use yii\db\Schema;
use yii\base\NotSupportedException;

class Generator extends \yii\gii\generators\model\Generator
{
    
    /**
     * @var array The attributes that should be ignored in the rules generation
     */
    public $ignoreAttributes = [
            'time', 'deleted'
        ];
    
    /**
     * @var array Possible names for images attributes
     */
    public $imagesAttributes = [
            'image', 'thumb', 'thumbnail', 'logo'
        ];
    
    /**
     *
     * @var array Posiible name of attributes that should be translated
     * Where key is the attribute name, and value is how it should be displayed in the Model behavior
     */
    public $translationAttributes = [
        'title'=>'title',
        'description'=>'description:text-editor', 
        'name'=>'name',
        'smallDescription'=>'smallDescription',
        'question'=>'question',
        'answer'=>'answer:text-editor'
    ];

    /**
     * @inheritdoc
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if(in_array($column->name, $this->ignoreAttributes) || in_array($column->name, $this->imagesAttributes)){
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
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
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        // Unique indexes rules
        try {
            $db = $this->getDbConnection();
            $uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount == 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        $lastLabel = array_pop($labels);
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['" . $columnsList . "'], 'unique', 'targetAttribute' => ['" . $columnsList . "'], 'message' => 'The combination of " . implode(', ', $labels) . " and " . $lastLabel . " has already been taken.']";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        return $rules;
    }
    
    /**
     * Returns images attributes for certain table.
     * Compare table attributes to [[imagesAttributes]] attribute
     * @param \yii\db\TableSchema $table
     * @return array
     */
    public function imagesAttributes($table){
        $array = [];
        foreach($table->columnNames as $name){
            if(in_array($name, $this->imagesAttributes)){
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
    public function translationAttributes($table){
        $array = [];
        $attributes = $this->translationAttributes;
        foreach($table->columnNames as $name){
            if(key_exists($name, $attributes)){
                $array[$name] = $attributes[$name];
            }
        }
        return $array;
    }

}
