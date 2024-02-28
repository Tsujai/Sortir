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

            ->add('dateHeureDebut', DateTimeType::class, [
                'label'=> 'Date et heure de début de l\'activité'
            ])

            ->add('duree', TextType::class, [
                'label' => 'Durée',
                'attr'=>[
                    'placeholder'=>'Durée de l\'activité en minutes'
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d{1,4}$/',
                        'message' => 'La durée doit être composée d\'au maximum 4 chiffres.'
                    ])
                ]
            ])
            ->add('dateLimiteInscription', DateType::class, [

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label'=>'Nombre de places',
                'attr'=>[
                    'placeholder'=>'Nombre maximum de participants'
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d{1,3}$/',
                        'message' => 'Le nombre de participants doit être composé d\'au maximum 3 chiffres.'
                    ])
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrez votre description de sortie',
                    'rows' => 5
                ]
            ])

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'query_builder'=> function(LieuRepository $lieuRepository){
                     return $lieuRepository->createQueryBuilder('l')
                            ->join('l.ville','v')
                            ->addSelect('v');
                },
                'choice_label' => function(Lieu $lieu){
                    return $lieu->getNom() . ' - (' . $lieu->getRue().' , '.$lieu->getVille()->getCodePostal().' '.$lieu->getVille()->getNom().')';
                },
                'placeholder' => '-- Entrer le lieu --'
            ])

            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier',
                'required' => false,
                'attr' => [
                    'checked' => 'checked',
                    'class' => 'form-check-input'
                ]
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']
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
