<?php

namespace abcms\generators\crud;

class Generator extends \yii\gii\generators\crud\Generator
{

    public $baseControllerClass = 'abcms\library\base\AdminController';

    /** @var array Possible names of attributes that should be generated as file fields * */
    public $fileAttributes = ['image', 'thumb', 'thumbnail', 'logo', 'background', 'image1', 'image2'];

    /** @var array Possible names of attributes that should be displayed as images * */
    public $imagesAttributes = ['image', 'thumb', 'thumbnail', 'logo', 'background', 'image1', 'image2'];

    /** @var array Possible names of attributes that should be ignored in view, index and search views * */
    public $ignoreAttributes = ['deleted'];

    /** @var string Field used to save active status * */
    public $activeAttribute = 'active';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ABCMS CRUD Generator';
    }

    /**
     * @inheritdoc
     */
    public function generateActiveField($attribute)
    {
        if($attribute == 'active') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }
        if(in_array($attribute, $this->fileAttributes)) {
            return "\$form->field(\$model, '$attribute')->fileInput()";
        }
        return parent::generateActiveField($attribute);
    }

    /**
     * Check if model has files attributes
     * @return boolean
     */
    public function hasFiles()
    {
        $result = FALSE;
        $names = $this->getColumnNames();
        foreach($names as $name) {
            if(in_array($name, $this->fileAttributes)) {
                return TRUE;
            }
        }
        return $result;
    }

    /**
     * Check if model has active field
     * @return boolean
     */
    public function hasActiveField()
    {
        return $this->hasField($this->activeAttribute);
    }

    /**
     * Return if the model has a ceratin field
     * @param string $fieldName
     * @return boolean
     */
    public function hasField($fieldName)
    {
        $result = FALSE;
        $names = $this->getColumnNames();
        foreach($names as $name) {
            if($name == $fieldName) {
                return TRUE;
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function generateActiveSearchField($attribute)
    {
        if($attribute == 'active') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }
        return parent::generateActiveSearchField($attribute);
    }

}
