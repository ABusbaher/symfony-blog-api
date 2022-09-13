<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i < 21; $i++) {
            $post = new BlogPost();
            $post->setTitle('Title # ' . $i);
            $post->setContent('This is content for post # ' . $i);
            $post->setAuthor('Author #' . mt_rand(1, 5));
            $post->setSlug('title-' . $i);
            $currentDateTime = new \DateTime();
            $postDateTime = $currentDateTime->modify('-' . mt_rand(1, 5) . ' day');
            $post->setPublished($postDateTime);
            $manager->persist($post);
        }

        $manager->flush();
    }
}
