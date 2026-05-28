<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\PrivateGroup;
use App\Repository\PrivateGroupRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateEventType extends AbstractType {


    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void {
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
            ])->add('group', EntityType::class, [
                'class' => PrivateGroup::class,
                'query_builder' => function (PrivateGroupRepository $gr): QueryBuilder {
                    return $gr->createQueryBuilder('g')
                        ->where('g.organiser = :user')
                        ->setParameter('user', $this->security->getUser())
                        ->orderBy('g.name', 'ASC');
                },
                'choice_label' => 'name',
                'mapped' => false,
                'placeholder' => 'Groupe prive (Laissez vide si vous voulez que la sortie soit publique) :',
                'required' => false,]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
