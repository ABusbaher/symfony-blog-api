<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    private const USERS = [
        [
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@a.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'enabled' => true,
        ],
        [
            'username' => 'user2',
            'name' => 'User #2',
            'email' => 'user@u2.com',
            'roles' => [User::ROLE_ADMIN],
            'enabled' => true,
        ],
        [
            'username' => 'user3',
            'name' => 'User #3',
            'email' => 'user@u3.com',
            'roles' => [User::ROLE_WRITER],
            'enabled' => true,
        ],
        [
            'username' => 'user4',
            'name' => 'User #4',
            'email' => 'user@u4.com',
            'roles' => [User::ROLE_EDITOR],
            'enabled' => true,
        ],
        [
            'username' => 'user5',
            'name' => 'User #5',
            'email' => 'user@u5.com',
            'roles' => [User::ROLE_COMMENTATOR],
            'enabled' => false,
        ],
    ];

    public function __construct(private UserPasswordHasherInterface $passwordHasher, private TokenGenerator $tokenGenerator)
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
        for ($i = 1; $i < 21; $i++) {
            $post = new BlogPost();
            $post->setTitle('Title # ' . $i);
            $post->setContent($this->faker->text(255));
            $authorReference = $this->getRandomUserReference($post);
            $post->setAuthor($authorReference);
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
                $authorReference = $this->getRandomUserReference($comment);
                $comment->setAuthor($authorReference);
                $comment->setPost($this->getReference("blog_post_$i"));
                $manager->persist($comment);
            }
        }
        $manager->flush();

    }

    /**
     * @throws Exception
     */
    public function loadUsers(ObjectManager $manager) :void
    {
        foreach (self::USERS as $userFixtures) {
            $user = new User();
            $user->setName($userFixtures['name']);
            $user->setUsername($userFixtures['username']);
            $user->setEmail($userFixtures['email']);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                '123456'
            );
            $user->setPassword($hashedPassword);
            $user->setRoles($userFixtures['roles']);
            $user->setEnabled($userFixtures['enabled']);
            if (!$userFixtures['enabled']) {
                $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());
            }
            $this->addReference($userFixtures['username'], $user);
            $manager->persist($user);
        }
        $manager->flush();
    }

    private function getRandomUserReference($entity): User
    {
        $randomUser = self::USERS[rand(0,4)];
        if ($entity instanceof BlogPost && !count(array_intersect($randomUser['roles'],
            [
                User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_WRITER
            ]))) {
            return $this->getRandomUserReference($entity);
        }
        if ($entity instanceof Comment && !count(array_intersect($randomUser['roles'],
                [
                    User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_WRITER, User::ROLE_COMMENTATOR
                ]))) {
            return $this->getRandomUserReference($entity);
        }

        return $this->getReference($randomUser['username']);
    }

}
