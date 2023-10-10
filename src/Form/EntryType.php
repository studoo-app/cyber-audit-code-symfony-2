<?php

namespace App\Form;

use App\Entity\Entry;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('login')
            ->add('password')
            ->add('url')
            ->add('tag',EntityType::class,[
                'class'=>Tag::class,
                'choices'=>$options["tags"],
                'choice_label'=>'label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
            'tags'=>[]
        ]);
    }
}
