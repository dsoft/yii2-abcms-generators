# Yii2 ABCMS Generators

## Installation
```bash
composer require abcms/yii2-generators
```
## Configuration
Add generators to your config file inside config `$config['modules']['gii']` array.

Replace `$config['modules']['gii'] = 'yii\gii\Module';` with the following:

```php
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
    'generators' => [
        'abcms-crud' => [
            'class' => 'abcms\generators\crud\Generator',
            'templates' => [
                'default' => '@vendor/abcms/yii2-generators/crud/default',
            ]
        ],
        'abcms-model' => [
            'class' => 'abcms\generators\model\Generator',
            'templates' => [
                'default' => '@vendor/abcms/yii2-generators/model/default',
            ]
        ],
    ],
];
```
