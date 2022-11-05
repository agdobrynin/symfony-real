<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
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
        /** @var User $user */
        $user = $options['data'];

        $builder->add('emoji', TextType::class, [
            'priority' => 3,
            'label' => 'my_profile.form.emoji.label',
            'help' => 'my_profile.form.emoji.help',
            'attr' => ['maxlength' => 2, 'readonly' => 'readonly'],
        ])
            ->add('email', EmailType::class, [
                'priority' => 5,
            ])
            ->add('userLocale', ChoiceType::class, [
                'required' => true,
                'priority' => 0,
                'mapped' => false,
                'label' => 'my_profile.form.locale',
                'choices' => HelperForm::getDataForChoiceType($this->locales),
                'data' => $user->getPreferences()->getLocale(),
            ])
            ->add('save', SubmitType::class, [
                'priority' => -1000,
                'label' => 'my_profile.form.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
