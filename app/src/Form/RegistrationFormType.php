<?php

namespace App\Form;

use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use function Symfony\Component\String\u;

class RegistrationFormType extends AbstractType
{
    private $locales;
    private $requestStack;

    public function __construct(LocalesInterface $locales, RequestStack $requestStack)
    {
        $this->locales = $locales;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choicesLocale = [];

        foreach ($this->locales->getLocales() as $locale) {
            $choicesLocale[(string)u(Languages::getName($locale))->title()] = $locale;
        }

        $builder->add('login', TextType::class, [
            'priority' => 1,
            'label' => 'registration_form.form.login.label',
            'help' => 'registration_form.form.login.help',
        ])
            ->add('nick', TextType::class, [
                'priority' => 2,
                'help' => 'registration_form.form.nick.help',
            ])
            ->add('emoji', TextType::class, [
                'priority' => 3,
                'label' => 'registration_form.form.emoji.label',
                'help' => 'registration_form.form.emoji.help',
                'attr' => ['maxlength' => 2, 'readonly' => 'readonly'],
            ])
            ->add('email', EmailType::class, [
                'priority' => 5,
            ])
            ->add('password', RepeatedType::class, [
                    'priority' => 6,
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
            ->add('locale', ChoiceType::class, [
                'required' => true,
                'priority' => 0,
                'mapped' => false,
                'label' => 'registration_form.form.locale',
                'choices' => $choicesLocale,
                'data' => $this->requestStack->getCurrentRequest()->getLocale(),
            ])
            ->add('save', SubmitType::class, [
                'priority' => -1000,
                'label' => 'registration_form.form.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
