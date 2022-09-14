<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $faker;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager) :void
    {
        $user = $this->getReference('admin_user');
        for ($i = 1; $i < 21; $i++) {
            $post = new BlogPost();
            $post->setTitle('Title # ' . $i);
            $post->setContent($this->faker->text(255));
            $post->setAuthor($user);
            $post->setSlug('title-' . $i);
            //$currentDateTime = new \DateTime();
            //$postDateTime = $currentDateTime->modify('-' . mt_rand(1, 5) . ' day');
            $post->setPublished($this->faker->dateTimeBetween('-3 years', '+3 weeks'));
            $this->setReference('blog_post_' . $i, $post);
            $manager->persist($post);
        }
        $manager->flush();
    }

    public function loadComments(ObjectManager $manager) :void
    {
        for ($i = 1; $i < 21; $i++) {
            for ($j = 0; $j < rand(1,5); $j++) {
                $comment = new Comment();
                $comment->setContent($this->faker->text());
                $comment->setPublished($this->faker->dateTimeBetween('-2 years', '+3 weeks'));
                $comment->setAuthor($this->getReference('admin_user'));
                $comment->setBlogPost($this->getReference("blog_post_$i"));
                $manager->persist($comment);
            }
        }
        $manager->flush();

    }

    public function loadUsers(ObjectManager $manager) :void
    {
        $adminUser = new User();
        $adminUser->setName('Admin');
        $adminUser->setUsername('admin');
        $adminUser->setEmail('admin@a.com');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $adminUser,
            '123456'
        );
        $adminUser->setPassword($hashedPassword);
        $this->addReference('admin_user', $adminUser);
        $manager->persist($adminUser);
        $manager->flush();
    }

}
