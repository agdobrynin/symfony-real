<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\EventSubscriber\FilterCommentSubscriber;
use App\Repository\MicroPostRepository;
use App\Service\MicroPost\GetMicroPostSoftDeleteServiceInterface;
use App\Service\MicroPost\SoftDeleteFilterServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/trash_bin")
 * @IsGranted(User::ROLE_ADMIN)
 */
class SoftDeletedPostsController extends AbstractController
{
    /**
     * @Route("/list", name="micro_post_trash_bin_list", methods={"get"})
     */
    public function index(Request $request, GetMicroPostSoftDeleteServiceInterface $microPostSoftDeleteService): Response
    {
        $page = (int)$request->get('page', 1);
        $microPostWithPaginationDto = $microPostSoftDeleteService->get(
            $page,
            $this->getParameter('micropost.page.size')
        );

        return $this->render('@mp/soft-deleted-posts.html.twig', compact('microPostWithPaginationDto'));
    }

    /**
     * @Route("/restore/{uuid}", name="micro_post_trash_bin_restore", methods={"get"})
     */
    public function restore(
        string                           $uuid,
        SoftDeleteFilterServiceInterface $softDeleteFilterService,
        MicroPostRepository              $microPostRepository
    ): RedirectResponse
    {
        $softDeleteFilterService->softDeleteOnlyOn();

        if ($microPost = $microPostRepository->find($uuid)) {
            $microPost->setDeleteAt(null);
            $microPostRepository->add($microPost, true);

            return $this->redirectToRoute('micro_post_view', [
                'uuid' => $microPost->getUuid(),
                FilterCommentSubscriber::GET_PARAMETER_SOFT_DELETE_DISABLED => true
            ]);
        }

        throw new NotFoundHttpException();
    }
}
