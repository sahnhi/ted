<?php

namespace App\Factory\View;

use App\View\ValidationErrorView;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidationErrorViewFactoryInterface
{
    public function create(ConstraintViolationListInterface $validationResults): ValidationErrorView;
}
