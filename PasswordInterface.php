<?php

namespace bupy7\password;

/**
 * @author Belosludcev Vasilij <bupy765@gmail.com>
 * @since 1.0.0
 */
interface PasswordInterface
{
    /**
     * Generating password hash from password and sets it to the model.
     * @param string $password Password for generation password hash.
     */
    public function setPassword($password);
    /**
     * Validate password with current.
     * @param string $password Password to validate
     * @return boolean If password provided is valid for current user.
     */
    public function validatePassword($password);
}