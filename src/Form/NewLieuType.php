<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewLieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'label'=>'Nom',
                'attr'=> [
                    'placeholder'=>'Nom du lieu'
                ]
            ])
            ->add('rue',TextType::class,[
                'label'=>'Rue',
                'attr'=> [
                    'placeholder'=>'Rue du lieu'
                ]
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => function(Ville $ville){
                    return $ville->getCodePostal().' '.$ville->getNom();
                },
                'placeholder'=>'-- Choisir une ville --'
            ])
            ->add('submit',SubmitType::class,[
                'label'=>'Enregistrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
