<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 15.03.18
 * Time: 13:29
 */

namespace Fnp\AdminBundle\Form;


use Fnp\AdminBundle\Entity\Post;
use Fnp\AdminBundle\Entity\PostCategory;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => PostCategory::class,
                'choice_label' => 'category',
                'attr' => [
                    'class' => 'validate',
                ],
                'label' => 'Category:',
                'required' => true,
            ])
            ->add('title', TextType::class,
                [
                    'label' => 'Title:',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'This value should not be blank.']),
                    ]
                ])
            ->add('content', CKEditorType::class, [
                    'config_name' => 'blog',
                    'label' => 'Content:',
            ])
            ->add('image', FileType::class,
                [
                    'data_class' => null,
                    'label' => ' ',
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '1m',
                            'mimeTypes' => [
                                "image/png",
                                "image/jpeg",
                                "image/jpg"
                            ],
                            'mimeTypesMessage' => '',
                            'maxSizeMessage' => '',

                        ]), new Image([
                            'maxWidth' => '1920',
                            'maxHeight' => '1080',
                            'maxWidthMessage' => 'Please upload a valid image size. Max size (1920x1080)',
                            'maxHeightMessage' => 'Please upload a valid image size. Max size (1920x1080)'
                        ])
                    ]
                ]
            )
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }
}