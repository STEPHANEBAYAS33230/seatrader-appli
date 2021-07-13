<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomDeLaSociete',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'nom Société*',]])
            //->add('roles')
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field form-control text-primary', 'placeholder'=>'Nouveau mot de passe*', 'style'=>'background-color: #fffcc8; border-radius: 15px;']],
                'required' => true,
                'first_options'  => ['label' => 'MP'],
                'second_options' => ['label' => 'Confirmation MP'],
            ])
            ->add('nom',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'nom*',]])
            ->add('prenom',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'prenom*',]])
            ->add('emailSociete',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'email Société*',]])
            ->add('emailPerso',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'email personnel',]])
            ->add('telephone_societe',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'tel Société*',]])
            ->add('telephonePerso',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'tel personnel',]])
            //->add('etatUilisateur')
            //->add('etatUtilisateur', EntityType::class, ['class' => EtatUtilisateur::class,'choice_value'=>'etatUtilisateur'])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
