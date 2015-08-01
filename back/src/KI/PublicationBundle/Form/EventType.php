<?php

namespace KI\PublicationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('text')
            ->add('startDate')
            ->add('endDate')
            ->add('entryMethod')
            ->add('shotgunDate')
            ->add('shotgunLimit')
            ->add('shotgunText')
            ->add('place')
            ->add('authorClub', 'club_selector')
            ->add('image', 'image_selector');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'data_class' => 'KI\PublicationBundle\Entity\Event'
        ));
    }

    public function getName()
    {
        return '';
    }
}
