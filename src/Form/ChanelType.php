<?php

namespace App\Form;

use App\Entity\Chanel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ChanelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Article|null $article */
        $chanel = $options['data'] ?? null;
        $isEdit = $chanel && $chanel->getId();

        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('url', TextType::class)
            ->add('imageFile', FileType::class, [
                'required' => false,
                'mapped' => false
            ])
        ;
        $imageConstraints = [
            new Image([
                'maxSize' => '5M'
            ])
        ];
        if (!$isEdit || !$chanel->getImageFilename()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Please upload an image',
            ]);
        }
        $builder
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => $imageConstraints
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chanel::class,
        ]);
    }
}
