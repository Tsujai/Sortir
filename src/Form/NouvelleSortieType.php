<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

class NouvelleSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Nom de la sortie',
                    'class' => 'fondProfil2',
                ]
            ])

            ->add('dateHeureDebut', DateTimeType::class, [
                'label'=> 'Date et heure de début de l\'activité',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'class' => 'fondProfil2',
                ]
            ])

            ->add('duree', TextType::class, [
                'label' => 'Durée',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder'=>'Durée de l\'activité en minutes',
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'class' => 'fondProfil2',
                ]

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label'=>'Nombre de places',
                'label_attr' => ['class' => 'fc1'],
                'attr'=>[
                    'placeholder'=>'Nombre maximum de participants',
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Entrez votre description de sortie',
                    'rows' => 5,
                    'class' => 'fondProfil2',
                ]
            ])

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'label'=> 'Lieu',
                'label_attr' => ['class' => 'fc1'],
                'query_builder'=> function(LieuRepository $lieuRepository){
                     return $lieuRepository->createQueryBuilder('l')
                            ->join('l.ville','v')
                            ->addSelect('v');
                },
                'choice_label' => function(Lieu $lieu){
                    return $lieu->getNom() . ' - (' . $lieu->getRue().' , '.$lieu->getVille()->getCodePostal().' '.$lieu->getVille()->getNom().')';
                },
                'placeholder' => '-- Entrer le lieu --',
                'attr' => [
                    'class' => 'fondProfil2',
                ]
            ])

            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier',
                'label_attr' => ['class'=> 'isPublished'],
                'required' => false,
                'attr' => [
                    'checked' => 'checked',
                    'class' => 'form-check-input fondProfil2'
                ]
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'custom-btn']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
