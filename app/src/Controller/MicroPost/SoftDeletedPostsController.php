<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\MicroPost;
use App\Entity\User;
use App\EventSubscriber\FilterCommentSubscriber;
use App\Service\MicroPost\MicroPostSoftDeleteServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/trash_bin")
 * @IsGranted(User::ROLE_ADMIN)
 */
class SoftDeletedPostsController extends AbstractController
{
    private $microPostSoftDeleteService;

    public function __construct(MicroPostSoftDeleteServiceInterface $microPostSoftDeleteService)
    {
        $this->microPostSoftDeleteService = $microPostSoftDeleteService;
    }

    /**
     * @Route("/list", name="micro_post_trash_bin_list", methods={"get"})
     */
    public function index(Request $request): Response
    {
        $page = (int)$request->get('page', 1);
        $microPostWithPaginationDto = $this->microPostSoftDeleteService->get($page);

        return $this->render('@mp/soft-deleted-posts.html.twig', compact('microPostWithPaginationDto'));
    }

    /**
     * @Route("/restore/{uuid}", name="micro_post_trash_bin_restore", methods={"get"})
     */
    public function restore(MicroPost $microPost): RedirectResponse
    {
        $this->microPostSoftDeleteService->restore($microPost);

        return $this->redirectToRoute('micro_post_view', [
            'uuid' => $microPost->getUuid(),
            FilterCommentSubscriber::GET_PARAMETER_SOFT_DELETE_DISABLED => true
        ]);
    }
}
