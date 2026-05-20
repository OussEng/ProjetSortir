<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => "Nom de la sortie : "])
            ->add('dateTimeStart', null, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie : '
            ])
            ->add('dateLimitRegistration', null, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription : "
            ])
            ->add('duration', DateIntervalType::class, ['label' => 'Durée : ', 'with_years' => false,
                'with_months' => false,
                'with_days' => true,
                'with_hours' => true,
                'with_minutes' => true,
                'with_seconds' => false,
                'labels' => [
                    'days'    => 'Jours',
                    'hours'   => 'Heures',
                    'minutes' => 'Minutes',
                ],])
            ->add('nbParticipants', TextType::class, ['label' => "Nombre de places : "])
            ->add('eventDescription', TextType::class, ['label' => "Description et infos : "])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'address',
                'label' => 'Lieu : ',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
