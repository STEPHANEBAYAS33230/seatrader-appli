<?php

namespace App\Form;

use App\Entity\MiseEnAvant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MiseEnAvantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('dateCreation')
            ->add('dateLivraisonMiseEnAvant', DateType::class,[
                'label' => 'livraison le ',
                'format' => 'dd-MM-yyyy'])
            ->add('prix')
            //->add('niveau')
            ->add('couleur')
            ->add('produitMiseEnAvant')
            ->add('origine')
            ->add('colisage')
            ->add('raison')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MiseEnAvant::class,
        ]);
    }
}
