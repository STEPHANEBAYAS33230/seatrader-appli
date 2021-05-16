<?php

namespace App\Form;

use App\Entity\EtatCommande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtatCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('etatCommande', EntityType::class,['class' =>EtatCommande::class,'choice_value'=>'etatCommande'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EtatCommande::class,
        ]);
    }
}
