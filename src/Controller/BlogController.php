<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Factory\JsonResponseFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/blog')]
class BlogController extends AbstractController
{
    public function __construct(private JsonResponseFactory $jsonResponseFactory,
                                private ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: "blog_list", requirements: ['page' => '\d+'], defaults: ['page' => 5], methods: ["GET"])]
    public function list(Request $request, $page = 1): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $repository = $this->doctrine->getRepository(BlogPost::class);
        $posts = $repository->findAll();

        return new JsonResponse(
            [
                'page' => $page,
                'limit' => $limit,
                'data' => array_map(function (BlogPost $post) {
                    return $this->generateUrl('blog_by_slug', ['slug' => $post->getSlug()]);
                }, $posts),
            ]
        );
    }

    #[Route('/{id}', name: "blog_by_id", requirements: ['id' => '\d+'], methods: ["GET"])]
    public function post(int $id): Response
    {
        $post = $this->doctrine->getRepository(BlogPost::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }
        return $this->jsonResponseFactory->create($post);
        //return $this->json($post);
    }

    #[Route('/{slug}', name: "blog_by_slug", methods: ["GET"])]
    public function postBySlug($slug): JsonResponse
    {
        $post = $this->doctrine->getRepository(BlogPost::class)->findOneBy(['slug' => $slug]);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for slug '.$slug
            );
        }
        return $this->json($post);
    }

    #[Route('/add', name: "blog_add", methods: ["POST"])]
    public function add(SerializerInterface $serializer, Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $em = $doctrine->getManager();
        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');
        $em->persist($blogPost);
        $em->flush();
        return $this->json($blogPost);
    }
}