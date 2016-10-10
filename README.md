# Yii2 ABCMS Generators

## Installation
```bash
composer require abcms/yii2-generators:dev-master
```
## Configuration
Add generators to your config file inside config $config['modules']['gii'] array:
```php
if(YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
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
}
```
