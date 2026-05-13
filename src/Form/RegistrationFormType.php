<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('nom', TextType::class, [
                'attr' => ['placeholder' => 'Votre nom']
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['placeholder' => 'Votre prénom']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'Email address']
            ])
            ->add('telephone', TelType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Ex: +216 XX XXX XXX']
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Votre adresse complète']
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['placeholder' => 'Minimum 6 caractères'],
                'constraints' => [
                    new NotBlank(message: 'Entrez un mot de passe'),
                    new Length(min: 6, minMessage: 'Minimum 6 caractères'),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Agree terms',
                'constraints' => [
                    new IsTrue(message: 'Acceptez les conditions.')
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
