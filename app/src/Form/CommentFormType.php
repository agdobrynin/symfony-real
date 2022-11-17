<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentFormType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fieldContentLength = $this->em
            ->getClassMetadata(Comment::class)
            ->fieldMappings['content']['length'] ?? 200;

        $builder->add('content', TextareaType::class, [
            'label' => 'Comment',
            'attr' => ['rows' => 3, 'maxlength' => $fieldContentLength]
        ])
            ->add('save', SubmitType::class, [
                'label' => 'Send new comment'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
