<?php

namespace KiwiBlade\Forms;

use InvalidArgumentException;

class FormFactory
{
    /**
     * @param string $action
     * @param string $formClass
     * @return Form
     */
    public function create($action, $formClass = null)
    {
        if (!$formClass) {
            $formClass = Form::class;
        }
        if (!is_a($formClass, Form::class, true)) {
            throw new InvalidArgumentException("$formClass is not a valid form class");
        }

        $form = new $formClass($action);

        return $form;
    }
}
