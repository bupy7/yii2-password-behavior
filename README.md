# IN DEVELOPING

yii2-password-behavior
======================
Behavior for change and create password of user account.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bupy7/yii2-password-behavior "*"
```

or add

```
"bupy7/yii2-password-behavior": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Implement your user identity model with
`bupy7\password\PasswordInterface` and add following code:

```php
use Yii;

/**
 * @inheritdoc
 */
public function validatePassword($password)
{
    return Yii::$app->security->validatePassword($password, $this->password);
}

/**
 * @inheritdoc
 */
public function setPassword($password)
{
    $this->password = Yii::$app->security->generatePasswordHash($password);
}
```

Attach behavior to model in your controller:

```php
use bupy7\password\PasswordBehavior;

$model->attachBehavior('passwordBehavior', [
    'class' => PasswordBehavior::className(),
    // other configurations
]);
```

##License

yii2-password-behavior is released under the BSD 3-Clause License.
