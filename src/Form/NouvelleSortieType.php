<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NouvelleSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => [
                    'placeholder' => 'Votre nom'
                ]
            ])

            ->add('dateHeureDebut', DateType::class, [
                'required' => false,
            ])

            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'required' => false
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'required' => false,
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'required' => false,
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrez votre description de sortie',
                    'rows' => 5
                ]
            ])
//            ->add('etat', CheckboxType::class, [
//                'label' => 'Published',
//                'required' => false,
//                'attr' => [
//                    'checked' => 'checked',
//                    'class' => 'form-check-input'
//                ]
//            ])

//            ->add('organisateur', EntityType::class, [
//                'required' => false,
//                'class' => Participant::class,
//                'choice_label' => 'id',
//            ])
//            ->add('site', EntityType::class, [
//                'label' => 'site.nom',
//                'required' => false,
//                'class' => Site::class
//            ])



            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'placeholder' => 'Choisir une ville',
                'choice_label' => 'nom',
                'mapped' => false,

            ])

            ->add('lieu', EntityType::class, [
                //'required' => false,
                'class' => Lieu::class,
                //'mapped' => false,
                'choice_label' => 'nom',
                'placeholder' => 'Entrer le lieu'

            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Published',
                'required' => false,
                'attr' => [
                    'checked' => 'checked',
                    'class' => 'form-check-input'
                ]
            ])


//            ->add('rue', TextType::class, [
//                'label' => 'Rue',
//                'required' => false,
//                'mapped' => false,
//                'attr' => [
//                    'placeholder' => 'entrer la rue'
//                ]
//            ])
//            ->add('cp', TextType::class, [
//                'label' => 'lieu.cp',
//                'required' => false,
//                'mapped' => false,
//                'attr' => [
//                    'placeholder' => 'entrer la cp'
//                ]
//
//            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);
    }
}
