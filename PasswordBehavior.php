<?php

namespace bupy7\password;

use Yii;
use yii\validators\Validator;
use app\components\ActiveRecord;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Behavior for change and create password of user account.
 * @author Vasilij Belosludcev <bupy765@gmail.com>
 * @since 1.0.0
 */
class PasswordBehavior extends Behavior
{
    /**
     * @var string Attribute name of old password.
     */
    public $oldPasswordAttribute = 'old_password';
    /**
     * @var string Attribute name of new password.
     */
    public $newPasswordAttribute = 'new_password';
    /**
     * @var string Attribute name of confirmed password.
     */
    public $confirmedPasswordAttribute = 'confirmed_password';
    /**
     * @var array Validation scenarios to which will be added to the validation rules.
     */
    public $scenarios = ['default'];
    /**
     * @var boolean Allow skip empty password field.
     */
    public $skipOnEmpty = false;
    /**
     * @var boolean Check old password before save.
     */
    public $checkPassword = false;
    /**
     * @var integer Minimum length of password.
     */
    public $minLenPassword;
    /**
     * @var integer Maximum length of password.
     */
    public $maxLenPassword;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }
    
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        if (!in_array($owner->scenario, $this->scenarios)) {
            return;
        }
        if (!($owner instanceof PasswordInterface)) {
            throw new InvalidConfigException('Class `' . get_class($owner) . '` must be an object implementing '
                . '`PasswordInterface`');
        }
        
        parent::attach($owner);
        
        $oldPasswordAttribute = $this->oldPasswordAttribute;
        $newPasswordAttribute = $this->newPasswordAttribute;
        $confirmedPasswordAttribute = $this->confirmedPasswordAttribute;
        
        // required
        if (!$this->skipOnEmpty && !$this->checkPassword) {
            $validator = Validator::createValidator(
                'required', 
                $owner, 
                [$newPasswordAttribute, $confirmedPasswordAttribute]
            );
            $owner->validators->append($validator);
        }
        // string
        $attributes = [$newPasswordAttribute, $confirmedPasswordAttribute];
        if ($this->checkPassword) {
            $attributes[] = $oldPasswordAttribute;
        }
        $validator = Validator::createValidator(
            'string', 
            $owner, 
            $attributes,
            [
                'min' => $this->minLenPassword,
                'max' => $this->maxLenPassword,
                'skipOnEmpty' => $this->skipOnEmpty,
            ]
        );
        $owner->validators->append($validator);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate($event)
    {
        $oldPasswordAttribute = $this->oldPasswordAttribute;
        $newPasswordAttribute = $this->newPasswordAttribute;
        $confirmedPasswordAttribute = $this->confirmedPasswordAttribute;
        
        // check old password
        if ($this->checkPassword && $this->owner->$newPasswordAttribute && $this->skipOnEmpty) {
            if (!empty($this->owner->$oldPasswordAttribute)) {
                if (!$this->owner->validatePassword($this->owner->$oldPasswordAttribute)) {
                    $this->owner->addError(
                        $oldPasswordAttribute, 
                        self::t(
                            'OLD_PASSWORD_ENTER_INCORRECT', 
                            [$this->owner->getAttributeLabel($oldPasswordAttribute)]
                        )
                    );
                }
            } else {
                $this->owner->addError(
                    $oldPasswordAttribute, 
                    self::t( 
                        'OLD_PASSWORD_EMPTY', 
                        [$this->owner->getAttributeLabel($oldPasswordAttribute)]
                    )
                );
            }
        }
        // compare password
        $validator = Validator::createValidator(
            'compare', 
            $this->owner, 
            [$confirmedPasswordAttribute], 
            [
                'compareAttribute' => $newPasswordAttribute,
                'skipOnEmpty' => empty($this->owner->$newPasswordAttribute),
            ]
        );
        $this->owner->validators->append($validator);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($event)
    {
        $newPasswordAttribute = $this->newPasswordAttribute;
        
        if (!empty($this->owner->$newPasswordAttribute)) {
            $this->owner->setPassword($this->owner->$newPasswordAttribute);
        }
    }
    
    /**
     * Registration of translation class.
     */
    protected function registerTranslations()
    {
        Yii::$app->i18n->translations['bupy7/password'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'forceTranslation' => true,
            'basePath' => '@bupy7/password/messages',
            'fileMap' => [
                'bupy7/password' => 'core.php',
            ],
        ];
    }
    
    /**
     * Translates a message to the specified language.
     * 
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current of application
     * language.
     * @return string
     */
    static public function t($message, $params = [], $language = null)
    {
        return Yii::t('bupy7/password', $message, $params, $language);
    }
}
