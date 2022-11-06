<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', PasswordType::class, [
            'mapped' => false,
            'label' => 'my_profile.password.form.label',
            'invalid_message' => 'aaa'
        ])
            ->add('password_new', RepeatedType::class, [
                    'mapped' => false,
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'my_profile.password.form.label_new_1'],
                    'second_options' => ['label' => 'my_profile.password.form.label_new_2'],
                    'invalid_message' => 'my_profile.form.password.invalid_message',
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
            ->add('save', SubmitType::class, [
                'priority' => -1000,
                'label' => 'my_profile.password.form.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
