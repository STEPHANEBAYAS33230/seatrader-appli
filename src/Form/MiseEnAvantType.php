<?php

namespace App\Form;

use App\Entity\MiseEnAvant;
use DateInterval;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MiseEnAvantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $today->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P30D'));
        $builder

            //->add('dateCreation')
            ->add('dateLivraisonMiseEnAvant', DateType::class,[
                'label' => 'livraison le ',
                'format' => 'dd-MM-yyyy',
                ])
            ->add('prix', NumberType::class,['scale'=>2])
            //->add('niveau')
            ->add('couleur', ChoiceType::class,['choices'=>['Bleu'=>'blue', 'Bleu clair'=>'aqua', 'Bisque'=>'bisque', 'Violet'=>'violet'
                ,'Rose'=>'pink','Vert'=>'green', 'Rouge'=>'red', 'Orange'=>'orange', 'Jaune'=>'yellow', 'Vert lime'=>'lime', 'Tomate'=>'tomato', 'Saumon'=>'salmon','Argent'=>'silver', 'Marron'=>'peru']])
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
