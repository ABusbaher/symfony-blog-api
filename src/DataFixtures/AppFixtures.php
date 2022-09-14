<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
    }

    public function loadBlogPosts(ObjectManager $manager) :void
    {
        $user = $this->getReference('admin_user');
        for ($i = 1; $i < 21; $i++) {
            $post = new BlogPost();
            $post->setTitle('Title # ' . $i);
            $post->setContent('This is content for post # ' . $i);
            $post->setAuthor($user);
            $post->setSlug('title-' . $i);
            $currentDateTime = new \DateTime();
            $postDateTime = $currentDateTime->modify('-' . mt_rand(1, 5) . ' day');
            $post->setPublished($postDateTime);
            $manager->persist($post);
        }
        $manager->flush();
    }

    public function loadComments(ObjectManager $manager) :void
    {

    }

    public function loadUsers(ObjectManager $manager) :void
    {
        $adminUser = new User();
        $adminUser->setName('Admin');
        $adminUser->setUsername('admin');
        $adminUser->setEmail('admin@a.com');
        $adminUser->setPassword('123456');
        $this->addReference('admin_user', $adminUser);
        $manager->persist($adminUser);
        $manager->flush();
    }

}
