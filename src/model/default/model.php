<?php

//是否需要软删除
$softDelete = false;
if (array_key_exists('delete_time', $properties)) $softDelete = true;

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;
<?php if ($softDelete): ?>

use \yii2tech\ar\softdelete\SoftDeleteBehavior;
use \yii2tech\ar\softdelete\SoftDeleteQueryBehavior;
<?php endif; ?>

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($properties as $property => $data): ?>
 * @property <?= "{$data['type']} \$$property"  . ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '') . "\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim(GII_BASE_MODEL_CLASS, '\\') . "\n" ?>
{
    public static function tableName(): string
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }

    public function rules(): array
    {
        return [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . ",\n        ") ?>];
    }

    public function attributeLabels(): array
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * {@inheritdoc}
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
<?php if ($softDelete): ?>

    public static function find()
    {
        $query = parent::find();

        return $query;
    }
<?php endif; ?>
}
