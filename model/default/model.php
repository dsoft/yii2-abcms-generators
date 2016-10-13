<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
$imagesAttributes = $generator->imagesAttributes($tableSchema);
$translationAttributes = $generator->translationAttributes($tableSchema);
$hasTimeAttribute = $generator->hasField($generator->timeAttribute, $tableSchema);
$hasIpAttribute = $generator->hasField($generator->ipAddressAttribute, $tableSchema);
?>

namespace <?= $generator->ns ?>;

use Yii;
<?php if($imagesAttributes): ?>
use abcms\library\behaviors\ImageUploadBehavior;
<?php endif; ?>
<?php if($hasTimeAttribute): ?>
use abcms\library\behaviors\TimeBehavior;
<?php endif; ?>
<?php if($hasIpAttribute): ?>
use abcms\library\behaviors\IpAddressBehavior;
<?php endif; ?>

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }

<?php if ($imagesAttributes || $translationAttributes || $hasTimeAttribute || $hasIpAttribute): ?>
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
<?php if($translationAttributes): ?>
            [
                'class' => \abcms\multilanguage\behaviors\ModelBehavior::className(),
                'attributes' => [
<?php foreach($translationAttributes as $translation): ?>
                    '<?php echo $translation; ?>',
<?php endforeach; ?>
                ],
            ],
<?php endif; ?>
<?php if($imagesAttributes):
foreach($imagesAttributes as $image): ?>
            [
                'class' => ImageUploadBehavior::className(),
                'attribute' => '<?php echo $image; ?>',
            ],
<?php endforeach;
endif; ?>
<?php if($hasTimeAttribute): ?>
            [
                'class' => TimeBehavior::className(),
            ],
<?php endif; ?>
<?php if($hasIpAttribute): ?>
            [
                'class' => IpAddressBehavior::className(),
            ],
<?php endif; ?>
        ]);
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
}
