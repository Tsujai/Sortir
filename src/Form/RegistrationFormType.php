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
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'fondProfil2',
                ]
            ])

            ->add('prenom', TextType::class, [
                'label' => 'Prénom :',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Votre prénom',
                    'class' => 'fondProfil2',
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'N° téléphone :',
                'label_attr' => ['class' => 'fc1'],
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre n° de téléphone',
                    'class' => 'fondProfil2',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Votre email',
                    'class' => 'fondProfil2',
                ]
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo :',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Votre pseudo',
                    'class' => 'fondProfil2',
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'label_attr' => ['class' => 'fc1'],
                'options' => [
                    'attr' => [
                        'class' => 'fondProfil2',
                    ],
                ],
                'first_options' => [
                    'label' => 'Mot de passe :',
                    'label_attr' => ['class' => 'fc1'],
                    'attr' => [
                        'placeholder' => 'Saisir mot de passe',
                         'class' => 'fondProfil2',
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
                    'label_attr' => ['class' => 'fc1'],
                    'attr' => [
                        'placeholder' => 'Confirmer le mot de passe',
                        'class' => 'fondProfil2',
                    ],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'mapped' => false,
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Ma photo :',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'class' => 'fondProfil2',

                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024K',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Ce format n\'est pas pris en charge',
                        'maxSizeMessage' => 'Ce ficher est trop lourd'
                    ]),
                ]
            ])
            ->add('sites', EntityType::class, [
                'mapped' => false,
                'class' => Site::class,
                'placeholder' => '-- Choisir un site --',
                'choice_label' => 'nom',
                'label' => 'Ville de rattachement :',
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'value' => 'id',
                    'class' => 'fondProfil2',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'custom-btn-login-register'
                ]
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
