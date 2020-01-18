<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 18.03.18
 * Time: 14:39
 */

namespace Fnp\AdminBundle\Form;

use Fnp\AdminBundle\Entity\PostCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', TextType::class,
                [
                    'label' => 'Category name:',
                    'attr' => [
                        'autofocus' => true,
                    ],
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'This value should not be blank.']),
                    ]
                ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostCategory::class
        ]);
    }
}