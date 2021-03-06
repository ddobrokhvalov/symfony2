<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory;
use Sonata\AdminBundle\Validator\ErrorElement;

class InlineValidator extends ConstraintValidator
{
    protected $container;

    public function __construct(ContainerInterface $container, ConstraintValidatorFactory $constraintValidatorFactory)
    {
        $this->container = $container;
        $this->constraintValidatorFactory = $constraintValidatorFactory;
    }

    public function isValid($value, Constraint $constraint)
    {
        if (is_string($constraint->getService())) {
            $service = $this->container->get($constraint->getService());
        } else {
            $service = $constraint->getService();
        }

        $errorElement = new ErrorElement(
            $value,
            $this->constraintValidatorFactory,
            $this->context,
            $this->context->getGroup()
        );

        call_user_func(array($service, $constraint->getMethod()), $errorElement, $value);

        return count($this->context->getViolations()) == 0;
    }
}