<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('firstname', TextType::class, [
                'disabled' => true,
                'label' => 'prénom'
                
            ])
            ->add('lastname', TextType::class, [
                'disabled' => true,
                'label' => 'nom'
                
            ])
            ->add('email', EmailType::class, [
                'disabled' => true,
                'label' => 'adresse email'
                
            ])
            ->add('old_password', PasswordType::class, [
               'label' => ' mot de passe',
               'mapped' => false,
               'attr' => [
                   'placeholder' => 'Saisissez votre mot de passe'
               ]
            ]) 
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Le mot de passe doit être identique!',
                'label' => ' Nouveau mot de passe',
                'required' => true,
                'first_options' => [ 
                    'label' => 'Nouveau mot de passe',
                'attr' => [
                    'placeholder' => 'Saisir votre nouveau mot de passe.'
                ]
            ],
                'second_options' => [
                    'label' => 'Confirmez votre nouveau mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirmer nouveau mot de passe.'
                    ]
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label'=> "Mettre à jour"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
