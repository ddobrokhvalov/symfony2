<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Form;

use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilder;

/**
 * This class is use to simulate the Form API
 *
 */
class FormMapper
{
    protected $formBuilder;

    protected $formContractor;

    protected $admin;

    protected $currentGroup;

    public function __construct(FormContractorInterface $formContractor, FormBuilder $formBuilder, AdminInterface $admin)
    {
        $this->formBuilder      = $formBuilder;
        $this->formContractor   = $formContractor;
        $this->admin            = $admin;
    }

    /**
     * @param string $name
     * @param array $options
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function with($name, array $options = array())
    {
        $formGroups = $this->admin->getFormGroups();
        if (!isset($formGroups[$name])) {
            $formGroups[$name] = array();
        }

        $formGroups[$name] = array_merge(array('collapsed' => false, 'fields' => array()), $formGroups[$name], $options);

        $this->admin->setFormGroups($formGroups);

        $this->currentGroup = $name;

        return $this;
    }

    /**
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function end()
    {
        $this->currentGroup = null;

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $options
     * @param array $fieldDescriptionOptions
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function add($name, $type = null, array $options = array(), array $fieldDescriptionOptions = array())
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel());
        }

        $formGroups = $this->admin->getFormGroups();
        $formGroups[$this->currentGroup]['fields'][$name] = $name;
        $this->admin->setFormGroups($formGroups);

        if (!isset($fieldDescriptionOptions['type']) && is_string($type)) {
            $fieldDescriptionOptions['type'] = $type;
        }

        $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
            $this->admin->getClass(),
            $name instanceof FormBuilder ? $name->getName() : $name,
            $fieldDescriptionOptions
        );

        $this->formContractor->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        $options = array_merge($options, $this->formContractor->getDefaultOptions($type, $fieldDescription));

        $this->admin->addFormFieldDescription($name instanceof FormBuilder ? $name->getName() : $name, $fieldDescription);

        if ($name instanceof FormBuilder) {
            $this->formBuilder->add($name);
        } else {
            $this->formBuilder->add($name, $type, $options);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return \Symfony\Component\Form\FieldInterface
     */
    public function get($name)
    {
        return $this->formBuilder->get($name);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->formBuilder->has($key);
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $this->admin->removeFormFieldDescription($key);
        $this->formBuilder->remove($key);
    }

    /**
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }

    /**
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param string $name
     * @param mixed $type
     * @param array $options
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function create($name, $type = null, array $options = array())
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    /**
     * @param array $helps
     * @return FormMapper
     */
    public function setHelps(array $helps = array())
    {
        foreach($helps as $name => $help) {
            if ($this->admin->hasFormFieldDescription($name)) {
                $this->admin->getFormFieldDescription($name)->setHelp($help);
            }
        }

        return $this;
    }
}