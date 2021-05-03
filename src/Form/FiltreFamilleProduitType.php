<?php

namespace App\Form;

use App\Entity\FamilleProduit;
use App\Entity\FiltreFamilleProduit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreFamilleProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', EntityType::class,['class' =>FamilleProduit::class,'choice_value'=>'nomFamille', 'placeholder'=> 'toutes les familles', 'required'=>false,])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FiltreFamilleProduit::class,
        ]);
    }
}
