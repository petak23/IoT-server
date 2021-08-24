<?php
declare(strict_types=1);

namespace App\Forms\User;

use Nette;

/**
 * Form renderer for user forms
 * Last change 24.08.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 * Inšpirované z: https://github.com/tomaj/nette-bootstrap-form
 */
class UserFormRenderer extends Nette\Forms\Rendering\DefaultFormRenderer {   
  public $wrappers = [
    'form' => [
        'container' => null,
    ],
    'error' => [
        'container' => 'ul class="error alert alert-danger text-center p-2"',
        'item' => 'li',
    ],
    'group' => [
        'container' => 'fieldset',
        'label' => 'legend',
        'description' => 'p',
    ],
    'controls' => [
        'container' => null,
    ],
    'pair' => [
        'container' => 'div class="form-group row"',
        '.required' => 'required',
        '.optional' => null,
        '.odd' => null,
        '.error' => 'has-danger',
    ],
    'control' => [
        'container' => 'div class=col-sm-8',
        '.odd' => null,
        '.multi' => null,
        'description' => 'class="col-12"',
        'requiredsuffix' => '',
        'errorcontainer' => 'span class=form-control-feedback',
        'erroritem' => '',
        '.required' => 'required',
        '.error' => 'is-invalid',
        '.text' => 'text',
        '.password' => 'text',
        '.file' => 'text',
        '.email' => 'text',
        '.number' => 'text',
        '.submit' => 'button',
        '.image' => 'imagebutton',
        '.button' => 'button',
    ],
    'label' => [
        'container' => 'div class="col-sm-4 col-form-label align-top text-right"',//ok
        'suffix' => null,
        'requiredsuffix' => '',
    ],
    'hidden' => [
        'container' => null,
    ],
  ];

  public function render(Nette\Forms\Form $form, string $mode = null): string {

    foreach ($form->getControls() as $control) {
      $type = $control->getOption('type');
      if ($type === 'button' && !(isset($control->getControlPrototype()->attrs['class']) && strlen($control->getControlPrototype()->attrs['class']))) {
        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
        $usedPrimary = true;
      } elseif ($type == 'text') {
        $control->getControlPrototype()->addClass('form-control');
      } elseif (in_array($type, ['checkbox', 'radio'], true)) {
        if ($control instanceof \Nette\Forms\Controls\Checkbox) {
          $control->getLabelPrototype()->addClass('form-check-label');
        } else {
          $control->getItemLabelPrototype()->addClass('form-check-label');
        }
        $control->getControlPrototype()->addClass('form-check-input');
        $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
      }
    }

    return parent::render($form, $mode);
  }
}