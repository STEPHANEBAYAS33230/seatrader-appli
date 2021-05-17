<?php

namespace App\Form;

use App\Entity\FamilleProduit;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomProduit')
            //->add('quantite')
            ->add('famille', EntityType::class,['class' =>FamilleProduit::class,'choice_value'=>'nomFamille', 'required'=>'true', 'attr' => ['value'=>'GROS POISSONS']])
            ->add('pieceOuKg', ChoiceType::class,['choices'=>['KG'=>'KG', 'PIECE(S)'=>'PIECE(S)']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
