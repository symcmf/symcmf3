<?php

namespace AuthBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UsersType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', EntityType::class, [
                'class' => 'AuthBundle:User',
                'choice_label' => 'email',
            ])
        ;

//        $builder
//            ->add('users', EntityType::class, [
//                'class' => 'AuthBundle:Role',
//                'choice_label' => 'name',
//                'multiple' => true,
//            ])
//        ;
    }
}
