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
        // safe
        $attributes = [$newPasswordAttribute, $confirmedPasswordAttribute];
        if ($this->checkPassword) {
            $attributes[] = $oldPasswordAttribute;
        }
        $validator = Validator::createValidator('safe', $owner, $attributes);
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
        if ($this->checkPassword && !empty($this->owner->$newPasswordAttribute) && !$this->skipOnEmpty) {
            if (!empty($this->owner->$oldPasswordAttribute)) {
                if (!$this->owner->validatePassword($this->owner->$oldPasswordAttribute)) {
                    $this->owner->addError(
                        $oldPasswordAttribute, 
                        Yii::t(
                            'yii',
                            '{attribute} is invalid.', 
                            ['attribute' => $this->owner->getAttributeLabel($oldPasswordAttribute)]
                        )
                    );
                }
            } else {
                $this->owner->addError(
                    $oldPasswordAttribute, 
                    Yii::t(
                        'yii',
                        '{attribute} cannot be blank.', 
                        ['attribute' => $this->owner->getAttributeLabel($oldPasswordAttribute)]
                    )
                );
            }
        }
        // compare
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
}
