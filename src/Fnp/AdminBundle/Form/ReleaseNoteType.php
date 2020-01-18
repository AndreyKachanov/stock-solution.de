<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 26.03.18
 * Time: 12:00
 */

namespace Fnp\AdminBundle\Form;


use Fnp\AdminBundle\Entity\ReleaseNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReleaseNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                    'label' => 'Title:',
                    'attr' => [
                        'autofocus' => true
                    ],
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'This value should not be blank.']),
                    ]
            ])
            ->add('content', TextareaType::class, [
                    'label' => 'Content:',
                    'attr' => [
                        'class' => 'tinymce not-active',
                        'data-theme' => 'advanced',
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'This value should not be blank.']),
                    ]
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReleaseNote::class
        ]);
    }
}