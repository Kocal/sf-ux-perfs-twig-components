<?php

namespace App\Twig\Components\App;

use App\Entity\Post;
use App\Security\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * The star in front of a post title, rendered once per row of the posts table.
 */
#[AsLiveComponent]
final class FeaturedToggle
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Post $post;

    #[LiveAction]
    public function toggle(EntityManagerInterface $entityManager, Security $security): void
    {
        if (!$security->isGranted(PostVoter::EDIT, $this->post)) {
            throw new AccessDeniedException('Only editors and the author can feature this post.');
        }

        $this->post->setFeatured(!$this->post->isFeatured());
        $entityManager->flush();

        $this->emit('post:saved', ['id' => $this->post->getId()]);
    }
}
