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

**Implement your user identity model with
`bupy7\password\PasswordInterface` and add following code:**

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

**Added following properties to your model:**

```php
public $old_password;
public $new_password;
public $confirmed_password;
```

**Attach behavior to model in your controller:**

```php
use bupy7\password\PasswordBehavior;

$model->attachBehavior('passwordBehavior', [
    'class' => PasswordBehavior::className(),
    // other configurations
]);
```

### If you want set password with checking old password

**In your controller:**

```php
use bupy7\password\PasswordBehavior;

$model->attachBehavior('passwordBehavior', [
    'class' => PasswordBehavior::className(),
    'skipOnEmpty' => true,
    'checkPassword' => true,
    'scenarios' => [$model->scenario],
]);
```

### If you want set new password without checking old password

**In your controller:**

```php
use bupy7\password\PasswordBehavior;

$model->attachBehavior('passwordBehavior', [
    'class' => PasswordBehavior::className(),
    'skipOnEmpty' => true,
    'checkPassword' => false,
    'scenarios' => [$model->scenario],
]);
```

### If password must be set (example, registration)

```php
use bupy7\password\PasswordBehavior;

$model->attachBehavior('passwordBehavior', [
    'class' => PasswordBehavior::className(),
    'skipOnEmpty' => false,
    'checkPassword' => false,
    'scenarios' => [$model->scenario],
]);
```

##License

yii2-password-behavior is released under the BSD 3-Clause License.
