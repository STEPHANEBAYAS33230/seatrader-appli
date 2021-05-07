<?php

namespace App\Form;

use App\Entity\FiltreDateMiseEnAvant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreDateMiseEnAvantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateMeA', DateType::class,[
                'format' => 'dd-MM-yyyy',
            ])
            ->add('datePlus', DateType::class,[
                'format' => 'dd-MM-yyyy',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FiltreDateMiseEnAvant::class,
        ]);
    }
}
