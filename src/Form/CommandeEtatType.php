<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeEtatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('jourDeLivraison')
            //->add('dateCreationCommande')
            //->add('note')
            //->add('object')
            ->add('etatCommande')
            //->add('utilisateur')
            //->add('listeProduits')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
