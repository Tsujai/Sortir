<?php

namespace App\Form;


use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListeSortiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'class' => Site::class,
                'placeholder'=> 'Choisir un site',
                'choice_label' => 'nom',
                'attr'=>[
                    'value'=>'id',
                    'class' => 'fondProfil2',
                    ],
            ])

            ->add('nom',TextType::class,[
                'required'=>false,
                'label'=>'Le nom de la sortie contient : ',
                'label_attr' => ['class' => 'fc1'],
                'attr'=>[
                    'placeholder'=>'🔍',
                    'class' => 'fondProfil2',
                ],
            ])

            ->add('firstDate', DateType::class,[
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'label'=> 'Entre ',
                'attr'=>[
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('secondDate',DateType::class,[
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'label'=> ' et ',
                'attr'=>[
                    'class' => 'fondProfil2',
                ],
            ])

            ->add('moiQuiOrganise',CheckboxType::class,[
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'label'=>'Sorties dont je suis organisateur(trice)',
                'attr'=>[
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('moiInscrit',CheckboxType::class,[
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'label'=>'Sorties auxquelles je suis inscrit(e)',
                'attr'=>[
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('moiPasInscrit',CheckboxType::class,[
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'label'=>'Sorties auxquelles je ne suis pas inscrit(e)',
                'attr'=>[
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('sortiesPassees',CheckboxType::class,[
                'required'=>false,
                'label_attr' => ['class' => 'fc1'],
                'label'=>'Sorties passées',
                'attr'=>[
                    'class' => 'fondProfil2',
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn custom-btn-login col-12 text-center mx-auto',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }


}
