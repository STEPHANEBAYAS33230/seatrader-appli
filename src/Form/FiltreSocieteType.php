<?php

namespace App\Form;

use App\Entity\FiltreSociete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',SearchType::class,[
                'attr' => ['class' => 'form-control mr-sm-2', 'type' => 'search', 'placeholder' => 'nom de la société...', 'aria-label' => 'le nom de la société...', 'value'=> ' ',]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FiltreSociete::class,
        ]);
    }
}
