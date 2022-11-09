<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', TextType::class, [
                'label' => 'login_form.form.login.label',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])
                ]
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'login_form.form.login.password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'login_form.form.login.button'
            ]);
    }
}
