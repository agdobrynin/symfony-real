<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\MicroPost;
use App\Entity\User;
use App\EventSubscriber\FilterCommentSubscriber;
use App\Repository\Filter\SoftDeleteFilter;
use App\Repository\Filter\SoftDeleteOnlyFilter;
use App\Service\MicroPost\GetMicroPostsServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
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
    private $em;
    private $getMicroPostsService;

    public function __construct(EntityManagerInterface $em, GetMicroPostsServiceInterface $getMicroPostsService)
    {
        $this->em = $em;
        //⚠ This route provided only for user with role "User::ROLE_ADMIN" see annotation for class.
        $this->em->getFilters()->disable(SoftDeleteFilter::NAME);
        $this->em->getFilters()->enable(SoftDeleteOnlyFilter::NAME);
        $this->getMicroPostsService = $getMicroPostsService;
    }

    /**
     * @Route("/list", name="micro_post_trash_bin_list", methods={"get"})
     */
    public function index(Request $request): Response
    {
        $page = (int)$request->get('page', 1);
        $microPostWithPaginationDto = $this->getMicroPostsService->findLastSoftDeletedMicroPostsOrderByDeleteAt($page);

        return $this->render('@mp/soft-deleted-posts.html.twig', compact('microPostWithPaginationDto'));
    }

    /**
     * @Route("/restore/{uuid}", name="micro_post_trash_bin_restore", methods={"get"})
     */
    public function restore(MicroPost $microPost): RedirectResponse
    {
        $microPost->setDeleteAt(null);
        $this->em->persist($microPost);
        $this->em->flush();

        return $this->redirectToRoute('micro_post_view', [
            'uuid' => $microPost->getUuid(),
            FilterCommentSubscriber::GET_PARAMETER_SOFT_DELETE_DISABLED => true
        ]);
    }
}
