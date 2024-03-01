<?php

namespace App\Form;

use App\Entity\Site;
use App\Repository\SerieRepository;
use App\Repository\SiteRepository;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,[
                'required' => true,
                'label' => "Nom de l'école",
                'label_attr' => ['class' => 'fc1'],
                'attr' => [
                    'placeholder' => 'Renseignez le nom du site',
                    'class' => 'fondProfil2'
                ],
        ])
            ->add('ajouter', SubmitType::class, [
                'attr' => [
                    'class' => 'custom-btn'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }
}
