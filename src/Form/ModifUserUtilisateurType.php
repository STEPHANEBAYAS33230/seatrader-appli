<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifUserUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomDeLaSociete',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'nom Société', 'readonly'=>true,]])
            //->add('roles')
            //->add('password')
            ->add('nom',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'nom',]])
            ->add('prenom',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'prenom',]])
            ->add('emailSociete',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'email Société', 'readonly'=>true,]])
            ->add('emailPerso',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'email personnel',]])
            ->add('telephone_societe',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'tel Société', 'readonly'=>true,]])
            ->add('telephonePerso',TextType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'tel personnel',]])
            //->add('etatUtilisateur')
        ;
    }
    /*
      ->add('nomDeLaSociete',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'nom Société',]])
            //->add('roles')
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field form-control text-primary', 'placeholder'=>'Nouveau mot de passe', 'style'=>'background-color: #fffcc8; border-radius: 15px;']],
                'required' => true,
                'first_options'  => ['label' => 'MP'],
                'second_options' => ['label' => 'Confirmation MP'],
            ])
            ->add('nom',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'nom',]])
            ->add('prenom',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'prenom',]])
            ->add('emailSociete',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'email Société',]])
            ->add('emailPerso',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'email personnel',]])
            ->add('telephone_societe',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'tel Société',]])
            ->add('telephonePerso',SearchType::class,['attr'=>['class'=>'form-control text-primary', 'style'=> 'background-color: #fffcc8; border-radius: 15px;', 'placeholder'=>'tel personnel',]])
            //->add('etatUilisateur')
     */

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
