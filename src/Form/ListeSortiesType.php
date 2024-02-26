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
                'class' => Site::class,
                'placeholder'=> 'Choisir un site',
                'choice_label' => 'nom',
                'attr'=>[
                    'value'=>'id'
                ]
            ])

            ->add('nom',TextType::class,[
                'required'=>false,
                'label'=>'Le nom de la sortie contient : ',
                'attr'=>[
                    'placeholder'=>'🔍 Search',
                ],
            ])

            ->add('firstDate', DateType::class,[
                'required'=>false,
                'label'=> 'Entre '
            ])
            ->add('secondDate',DateType::class,[
                'required'=>false,
                'label'=> ' et '
            ])

            ->add('moiQuiOrganise',CheckboxType::class,[
                'required'=>false,
                'label'=>'Sorties dont je suis organisateur(trice)',
            ])
            ->add('moiInscrit',CheckboxType::class,[
                'required'=>false,
                'label'=>'Sorties auxquelles je suis inscrit(e)',
            ])
            ->add('moiPasInscrit',CheckboxType::class,[
                'required'=>false,
                'label'=>'Sorties auxquelles je ne suis pas inscrit(e)',
            ])
            ->add('sortiesPassees',CheckboxType::class,[
                'required'=>false,
                'label'=>'Sorties passées',
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary col-12 text-center mx-auto',
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
