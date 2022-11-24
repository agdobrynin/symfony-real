<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfirmResendFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'help' => 'confirm_token_resend.form.email.help',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5]),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'confirm_token_resend.form.submit',
            ]);
    }
}
