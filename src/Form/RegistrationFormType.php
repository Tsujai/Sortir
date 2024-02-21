<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom :',
                'attr' => [
                    'placeholder' => 'Votre nom'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :',
                'attr' => [
                    'placeholder' => 'Votre prénom'
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'N° téléphone :',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre n° de téléphone'
                ]
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email :',
                'attr' => [
                    'placeholder' => 'Votre email'
                ]
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo :',
                'attr' => [
                    'placeholder' => 'Votre pseudo'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [

                    ],
                ],
                'first_options' => [
                    'label' => 'Mot de passe :',
                    'attr' => [
                        'placeholder' => 'Saisir mot de passe'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrer un mot de passe'
                        ]),
                        new Length([
                            'min' => 4,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation :',
                    'attr' => [
                        'placeholder' => 'Confirmer le mot de passe'
                    ],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'mapped' => false,
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Ma photo :',
                'constraints' => [
                    new File([
                        'maxSize' => '1024K',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Ce format n\'est pas pris en charge',
                        'maxSizeMessage' => 'Ce ficher est trop lourd'
                    ]),
                ]
            ])
            ->add('sites', EntityType::class, [
                'mapped' => false,
                'required' => false,
                'class' => Site::class,
                'placeholder' => '-- Choisir un site --',
                'choice_label' => 'nom',
                'label' => 'Ville de rattachement :',
                'attr' => [
                    'value' => 'id'
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'sites' => Site::class
        ]);
    }
}
