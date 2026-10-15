<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Post;
use App\Model\PostCategory;
use App\Model\PostStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Post>
 */
final class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['placeholder' => 'How we made our dashboard 10x faster'],
            ])
            ->add('excerpt', TextareaType::class, [
                'attr' => ['rows' => 3, 'placeholder' => 'A short summary shown in listings'],
                'help' => 'Up to 280 characters.',
            ])
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose an author',
                'query_builder' => static fn (EntityRepository $authors) => $authors->createQueryBuilder('a')->orderBy('a.name'),
            ])
            ->add('category', EnumType::class, [
                'class' => PostCategory::class,
                'choice_label' => static fn (PostCategory $category): string => $category->label(),
                'placeholder' => 'Choose a category',
            ])
            ->add('status', EnumType::class, [
                'class' => PostStatus::class,
                'choice_label' => static fn (PostStatus $status): string => $status->label(),
                'expanded' => true,
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Publication date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('tags', ChoiceType::class, [
                'choices' => array_combine(Post::TAGS, Post::TAGS),
                'multiple' => true,
                'expanded' => true,
                'help' => 'Between 1 and 3 tags.',
            ])
            ->add('readingTime', IntegerType::class, [
                'label' => 'Reading time (minutes)',
                'attr' => ['min' => 1, 'max' => 60],
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Feature this post on the homepage',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            // LiveComponent requests already require the X-Requested-With header, which a cross-site form cannot send
            'csrf_protection' => false,
        ]);
    }
}
