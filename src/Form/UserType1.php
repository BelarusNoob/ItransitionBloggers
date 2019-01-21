<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class UserType1 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'label.firstName',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'label.lastName',
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('info', TextType::class, [
                'label' => 'label.info',
            ])
            ->add('facebook', TextType::class, [
                'label' => 'label.facebook',
            ])
            ->add('twitter', TextType::class, [
                'label' => 'label.twitter',
            ])
            ->add('pinterest', TextType::class, [
                'label' => 'label.pinterest',
            ])
            ->add('instagram', TextType::class, [
                'label' => 'label.instagram',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
