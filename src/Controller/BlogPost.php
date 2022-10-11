<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\SimplePostDto;
use App\Service\PostStoreInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/blog")
 */
class BlogPost extends AbstractController
{
    private $posts;

    public function __construct(PostStoreInterface $posts)
    {
        $this->posts = $posts;
    }

    /**
     * @Route("/", methods={"get"}, name="blog_list")
     */
    public function list(): Response
    {
        return $this->render('blog/posts.html.twig', ['posts' => $this->posts->list()]);
    }

    /**
     * @Route("/show/{uuid}", methods={"get"}, name="blog_show")
     */
    public function show(string $uuid): Response
    {
        if ($post = $this->posts->get($uuid)) {
            return $this->render('blog/post.html.twig', ['post' => $post]);
        }

        $errorMessage = sprintf('Post with uuid %s not found', $uuid);

        throw new NotFoundHttpException($errorMessage);
    }

    /**
     * @Route("/gen/{count}", methods={"get"})
     */
    public function gen(int $count = 10): RedirectResponse
    {
        $this->posts->destroy();
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < $count; $i++) {
            $dto = new SimplePostDto();
            $dto->title = $faker->realTextBetween(20, 39);
            $dto->content = $faker->realTextBetween(20, 600);
            $dto->date = $faker->dateTimeInInterval('-30 day', 'now');
            $this->posts->add($dto);
        }

        return $this->redirectToRoute("blog_list");
    }
}
