<?php

namespace App\Twig\Components\App;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Creates or edits a post, validating each field as soon as it changes.
 */
#[AsLiveComponent]
final class PostForm extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?Post $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(PostType::class, $this->initialFormData ?? new Post());
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        $this->submitForm();

        /** @var Post $post */
        $post = $this->getForm()->getData();
        $entityManager->persist($post);
        $entityManager->flush();

        $this->emit('post:saved', ['id' => $post->getId()]);
        $this->dispatchBrowserEvent('post:saved');

        if (null === $this->initialFormData) {
            $this->resetForm();
        }
    }
}
