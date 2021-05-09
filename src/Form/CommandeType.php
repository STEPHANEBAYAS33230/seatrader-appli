<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('jourDeLivraison',DateType::class,[
                'format' => 'dd-MM-yyyy',
            ])
            //->add('dateCreationCommande')
            ->add('note',TextareaType::class,['attr'=>['row'=>8, 'column'=>60]])
            //->add('etatCommande')
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
