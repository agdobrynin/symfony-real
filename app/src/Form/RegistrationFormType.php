<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('login', TextType::class, [
            'label' => 'registration_form.form.login.label',
            'help' => 'registration_form.form.login.help',
        ])
            ->add('nick', TextType::class, [
                'help' => 'registration_form.form.nick.help',
            ])
            ->add('emoji', TextType::class, [
                'label' => 'registration_form.form.emoji.label',
                'help' => 'registration_form.form.emoji.help',
                'attr' => ['maxlength' => 2, 'readonly' => 'readonly'],
            ])
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'registration_form.form.password.label_1'],
                    'second_options' => ['label' => 'registration_form.form.password.label_2'],
                    'invalid_message' => 'registration_form.form.password.invalid_message',
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(),
                        new Length([
                            'min' => 6,
                            'max' => 4096,
                        ]),
                    ]
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'registration_form.form.submit']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
