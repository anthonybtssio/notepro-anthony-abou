<?php

namespace App\Form;

use App\Entity\ClassLevel;
use App\Entity\Evaluation;
use App\Entity\Professor;
use App\Entity\Subject;
use App\Repository\ClassLevelRepository;
use App\Repository\SubjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $eval = $options['data'];
        $prof = $eval->getProfessor();

        // Ces variables ne semblent pas utilisées directement dans le builder ci-dessous,
        // mais je les laisse telles quelles pour ne pas casser votre logique existante.
        $subjects = $prof->getSubjects();
        $classes = [];
        $classes = $prof->getClassLevels();

        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l\'évaluation', // J'ai ajouté un label pour la clarté (optionnel)
            ])
            // --- DÉBUT MODIFICATION US 2 ---
            ->add('date_affichage', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'affichage des notes', // Libellé pour le prof
                'required' => true, // Obligatoire pour contrôler la visibilité
            ])
            // --- FIN MODIFICATION US 2 ---
            ->add('label', TextType::class, [
                'label' => 'Titre de l\'évaluation'
            ])
            ->add('bareme', IntegerType::class, [
                'label' => 'Barème (/20, /10, ...)',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('subject', EntityType::class, [
                'class' => Subject::class,
                'label' => 'Matière',
                'choice_label' => 'label',
                'expanded' => false,
                'multiple' => false,
                'query_builder' => function(SubjectRepository $er) use($prof){
                    return $er->findByProfessor($prof);
                },
            ])
            ->add('classLevel', EntityType::class, [
                'class' => ClassLevel::class,
                'label' => 'Classe',
                'choice_label' => 'label',
                'expanded' => false,
                'multiple' => false,
                'query_builder' => function(ClassLevelRepository $er) use($prof){
                    return $er->findByProfessor($prof);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
        ]);
    }
}

