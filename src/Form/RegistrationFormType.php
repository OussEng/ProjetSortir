<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email' )
            ->add('userName', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add('firstName', TextType::class, ['label' => "Prenom"])
            ->add('lastName', TextType::class, ['label' => "Nom"])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new NotBlank(
                        message: 'Saisissez un mot de passe',
                    ),
                    new Length(
                        min: 6,
                        max: 4096,
                        // max length allowed by Symfony for security reasons
                        minMessage: 'Le mot de pass doit contenir au moins {{ limit }} charactèrs',
                    ),
                ],
            ])
            ->add('phone', TextType::class, ['label' => 'Numéro de téléphone'])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les termes',
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Il faut accepter les termes et conditions',
                    ),
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
